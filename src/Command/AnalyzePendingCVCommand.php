<?php

namespace App\Command;

use App\Entity\JobApplication;
use App\Service\CVAnalyzerService;
use App\Service\CVKeywordScorerService;
use App\Service\CVParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:analyze-cvs',
    description: 'Analyse toutes les candidatures en attente via l\'IA et le scoring automatique.',
)]
class AnalyzePendingCVCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private CVParserService $cvParser;
    private CVKeywordScorerService $keywordScorer;
    private CVAnalyzerService $cvAnalyzer;

    public function __construct(
        EntityManagerInterface $entityManager,
        CVParserService $cvParser,
        CVKeywordScorerService $keywordScorer,
        CVAnalyzerService $cvAnalyzer
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->cvParser = $cvParser;
        $this->keywordScorer = $keywordScorer;
        $this->cvAnalyzer = $cvAnalyzer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Robot d\'Analyse SyfonuRH - Mode Auto-Pilote');

        // Rechercher les candidatures n'ayant pas encore été analysées
        $applications = $this->entityManager->getRepository(JobApplication::class)->findBy([
            'aiScore' => null
        ]);

        if (empty($applications)) {
            $io->success('Toutes les candidatures sont déjà analysées !');
            return Command::SUCCESS;
        }

        $io->note(sprintf('Trouvé %d candidature(s) à analyser.', count($applications)));
        $io->progressStart(count($applications));

        foreach ($applications as $app) {
            $cvPath = $app->getCvPath();
            if (!$cvPath) {
                $io->warning(sprintf('Candidature #%d : Aucun CV trouvé.', $app->getId()));
                $io->progressAdvance();
                continue;
            }

            try {
                // 1. Extraction du texte
                $cvText = $this->cvParser->extractTextFromPdf($cvPath);
                
                if ($cvText && !str_starts_with($cvText, 'Erreur')) {
                    $jobDesc = $app->getJobOffre()->getDescription() ?? '';
                    
                    // 2. Scoring Initial
                    $result = $this->keywordScorer->analyze($jobDesc, $cvText);
                    
                    // 3. IA (Gemini) si score faible pour affiner
                    if ($result['score'] < 40) {
                        $aiResult = $this->cvAnalyzer->analyze($jobDesc, $cvText);
                        if (!isset($aiResult['error'])) {
                            $result = $aiResult;
                        }
                    }

                    // 4. Enregistrement
                    $app->setAiScore($result['score']);
                    $app->setAiAnalysis(json_encode($result, JSON_UNESCAPED_UNICODE));
                    $app->setAiAnalyzedAt(new \DateTime());
                } else {
                    $io->warning(sprintf('Candidature #%d : PDF illisible automatiquement.', $app->getId()));
                }

            } catch (\Exception $e) {
                $io->error(sprintf('Erreur sur candidature #%d : %s', $app->getId(), $e->getMessage()));
            }

            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();
        $io->success('Le robot a terminé l\'analyse du pipeline !');

        return Command::SUCCESS;
    }
}
