<?php

namespace App\Service;

use Exception;

class GeminiService
{
    private string $apiKey;

    // Models to try in priority order
    private array $modelCandidates = [
        'gemini-1.5-flash',
        'gemini-1.5-pro',
        'gemini-1.0-pro',
        'gemini-pro',
        'gemini-2.0-flash-lite',
    ];

    public function __construct(string $geminiApiKey)
    {
        $this->apiKey = $geminiApiKey;
    }

    private function curlPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("Erreur CURL: " . $error);
        }
        return ['code' => $httpCode, 'body' => $response];
    }

    private function getAvailableModel(): string
    {
        // Try each candidate model to find one that works
        foreach ($this->modelCandidates as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $this->apiKey;
            $testData = [
                "contents" => [["parts" => [["text" => "test"]]]]
            ];

            $result = $this->curlPost($url, $testData);
            // 200 = works, 400 = works (bad request format but model found)
            // 429 = quota exceeded, but model exists
            // 404 = model not found, try next
            if ($result['code'] !== 404) {
                return $model;
            }
        }
        throw new Exception("Aucun modèle Gemini disponible avec cette clé API. Vérifiez votre quota sur https://aistudio.google.com");
    }

    public function generatePdfTemplate(string $userPrompt): array
    {
        if (!$this->apiKey) {
            return $this->generateLocalTemplate($userPrompt);
        }

        $model = $this->getAvailableModel();
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $this->apiKey;

        $systemInstruction = "Tu es un expert en design de documents RH et en HTML/CSS pour PDF (Dompdf). 
        Ta mission est de générer les segments d'un modèle de contrat de travail en format JSON.
        CONTRAINTES :
        1. Tu dois retourner UNIQUEMENT un objet JSON valide avec les clés : 'header', 'body', 'footer'.
        2. Le contenu de chaque clé doit être du HTML/CSS valide, compatible avec Dompdf (utilise uniquement des styles en ligne).
        3. Utilise impérativement ces placeholders : {{candidate_name}}, {{recruiter_name}}, {{salary}}, {{today}}, {{start_date}}, {{job_title}}, {{contract_id}}.
        4. Le design doit être Premium, élégant et professionnel.
        5. N'ajoute aucune explication en dehors du JSON.";

        $data = [
            "contents" => [[
                "parts" => [["text" => $systemInstruction . "\n\nPrompt utilisateur : " . $userPrompt]]
            ]]
        ];

        try {
            $result = $this->curlPost($url, $data);
            if ($result['code'] === 200) {
                $apiResult = json_decode($result['body'], true);
                $textResponse = $apiResult['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if (!empty($textResponse)) {
                    $textResponse = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($textResponse));
                    $jsonResponse = json_decode($textResponse, true);
                    if ($jsonResponse && isset($jsonResponse['body'])) {
                        return [
                            'header' => $jsonResponse['header'] ?? '',
                            'body' => $jsonResponse['body'] ?? '',
                            'footer' => $jsonResponse['footer'] ?? ''
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Fall through to local generation
        }

        // ── Fallback: Local intelligent generation ──
        return $this->generateLocalTemplate($userPrompt);
    }

    private function generateLocalTemplate(string $prompt): array
    {
        $p = strtolower($prompt);

        // --- Color detection ---
        $color = '#1e40af'; // default blue
        $colorLight = '#eff6ff';
        $colorBorder = '#3b82f6';
        if (str_contains($p, 'rouge') || str_contains($p, 'red')) { $color = '#9f1239'; $colorLight = '#fff1f2'; $colorBorder = '#e11d48'; }
        elseif (str_contains($p, 'vert') || str_contains($p, 'green')) { $color = '#15803d'; $colorLight = '#f0fdf4'; $colorBorder = '#22c55e'; }
        elseif (str_contains($p, 'noir') || str_contains($p, 'dark') || str_contains($p, 'black')) { $color = '#1a1a1a'; $colorLight = '#f4f4f5'; $colorBorder = '#52525b'; }
        elseif (str_contains($p, 'violet') || str_contains($p, 'purple')) { $color = '#6d28d9'; $colorLight = '#f5f3ff'; $colorBorder = '#8b5cf6'; }
        elseif (str_contains($p, 'orange')) { $color = '#c2410c'; $colorLight = '#fff7ed'; $colorBorder = '#f97316'; }
        elseif (str_contains($p, 'or') || str_contains($p, 'gold') || str_contains($p, 'luxe')) { $color = '#92400e'; $colorLight = '#fffbeb'; $colorBorder = '#d97706'; }
        elseif (str_contains($p, 'turquoise') || str_contains($p, 'cyan')) { $color = '#0e7490'; $colorLight = '#ecfeff'; $colorBorder = '#06b6d4'; }

        // --- Style detection ---
        $isMinimal = str_contains($p, 'minimal') || str_contains($p, 'simple') || str_contains($p, 'épuré');
        $isBold = str_contains($p, 'bold') || str_contains($p, 'gras') || str_contains($p, 'imposant') || str_contains($p, 'fort');
        $isLuxury = str_contains($p, 'luxe') || str_contains($p, 'premium') || str_contains($p, 'luxury') || str_contains($p, 'or') || str_contains($p, 'gold');
        $isModern = str_contains($p, 'modern') || str_contains($p, 'tech') || str_contains($p, 'startup') || str_contains($p, 'digital');

        if ($isLuxury) return $this->templateLuxury($color, $colorLight, $colorBorder);
        if ($isBold)   return $this->templateBold($color, $colorLight, $colorBorder);
        if ($isMinimal) return $this->templateMinimal($color, $colorBorder);
        if ($isModern)  return $this->templateModern($color, $colorLight, $colorBorder);

        return $this->templateClassic($color, $colorLight, $colorBorder);
    }

    private function templateClassic(string $c, string $cl, string $cb): array
    {
        return [
            'header' => "<div style=\"border-bottom: 3px solid {$c}; padding-bottom:18px; margin-bottom:25px;\"><table style=\"width:100%; border-collapse:collapse;\"><tr><td style=\"vertical-align:middle;\"><h1 style=\"color:{$c}; margin:0; font-size:26px; text-transform:uppercase; letter-spacing:2px;\">Contrat de Travail</h1><p style=\"color:#666; font-size:12px; margin:5px 0 0 0;\">Réf: {{contract_id}} — Établi le {{today}}</p></td><td style=\"text-align:right; vertical-align:middle;\"><div style=\"font-weight:bold; font-size:15px; color:{$c};\">SyfonuRH Corp.</div><div style=\"font-size:11px; color:#888;\">Département Ressources Humaines</div></td></tr></table></div>",
            'body' => "<div style=\"font-family:Arial,sans-serif; color:#333; line-height:1.8; text-align:justify;\"><h2 style=\"font-size:14px; color:{$c}; border-left:4px solid {$cb}; padding-left:10px; margin-top:25px;\">I. PARTIES CONTRACTANTES</h2><p>Le présent contrat est établi entre <strong>{{recruiter_name}}</strong>, ci-après «&nbsp;l'Employeur&nbsp;», et <strong>{{candidate_name}}</strong>, ci-après «&nbsp;le Collaborateur&nbsp;».</p><h2 style=\"font-size:14px; color:{$c}; border-left:4px solid {$cb}; padding-left:10px; margin-top:25px;\">II. POSTE ET RÉMUNÉRATION</h2><p>Le Collaborateur est engagé au poste de <strong>{{job_title}}</strong>, à compter du <strong>{{start_date}}</strong>, pour une rémunération brute mensuelle de <strong>{{salary}} TND</strong>.</p><h2 style=\"font-size:14px; color:{$c}; border-left:4px solid {$cb}; padding-left:10px; margin-top:25px;\">III. OBLIGATIONS RÉCIPROQUES</h2><p>Les deux parties s'engagent à respecter les obligations légales, réglementaires et conventionnelles en vigueur, notamment en matière de confidentialité et de propriété intellectuelle.</p><table style=\"width:100%; margin-top:50px; border-collapse:collapse;\"><tr><td style=\"width:50%; padding-right:15px; vertical-align:top;\"><div style=\"font-size:11px; font-weight:bold; text-transform:uppercase; color:#888; margin-bottom:10px;\">Pour l'Employeur</div><div style=\"border:1px solid #ddd; height:90px; background:{$cl}; padding:8px; font-size:10px; color:#bbb;\">Cachet &amp; Signature</div></td><td style=\"width:50%; padding-left:15px; vertical-align:top;\"><div style=\"font-size:11px; font-weight:bold; text-transform:uppercase; color:#888; margin-bottom:10px;\">Le Collaborateur — Lu et approuvé</div><div style=\"border:1px solid #ddd; height:90px; padding:8px; font-size:10px; color:#bbb; text-align:right;\"><strong style=\"color:#333;\">{{candidate_name}}</strong></div></td></tr></table></div>",
            'footer' => "<div style=\"margin-top:30px; padding-top:12px; border-top:1px solid #eee; font-size:9px; color:#aaa; text-align:center;\"><p>Document généré numériquement le {{today}} — SyfonuRH © 2025 — Réf: {{contract_id}}</p><p>Ce document constitue un acte contractuel légalement opposable entre les parties signataires.</p></div>"
        ];
    }

    private function templateLuxury(string $c, string $cl, string $cb): array
    {
        return [
            'header' => "<div style=\"background:{$c}; color:#fff; padding:30px; text-align:center; margin-bottom:25px;\"><h1 style=\"margin:0; font-size:28px; letter-spacing:4px; text-transform:uppercase;\">★ Contrat de Travail ★</h1><p style=\"margin:8px 0 0 0; font-size:12px; opacity:0.8;\">Document Officiel Premium — ID: {{contract_id}}</p></div>",
            'body' => "<div style=\"font-family:Georgia,serif; color:#1a1a1a; line-height:1.9;\"><div style=\"border:2px solid {$cb}; padding:20px; margin-bottom:25px; background:{$cl};\"><h2 style=\"color:{$c}; font-size:14px; text-transform:uppercase; margin-top:0;\">I — Parties Contractantes</h2><p>Entre <strong>{{recruiter_name}}</strong> (l'Employeur) et <strong>{{candidate_name}}</strong> (le Collaborateur), il a été convenu ce qui suit :</p></div><div style=\"border:2px solid {$cb}; padding:20px; margin-bottom:25px; background:#fff;\"><h2 style=\"color:{$c}; font-size:14px; text-transform:uppercase; margin-top:0;\">II — Poste &amp; Rémunération</h2><p>Poste : <strong>{{job_title}}</strong> — Début : <strong>{{start_date}}</strong> — Salaire brut : <strong>{{salary}} TND</strong></p></div><table style=\"width:100%; margin-top:50px; border-collapse:collapse;\"><tr><td style=\"width:50%; padding-right:15px;\"><div style=\"font-size:11px; font-weight:bold; color:{$c}; text-transform:uppercase; margin-bottom:10px;\">L'Employeur</div><div style=\"border:2px solid {$cb}; height:90px; background:{$cl}; padding:8px;\"></div></td><td style=\"width:50%; padding-left:15px;\"><div style=\"font-size:11px; font-weight:bold; color:{$c}; text-transform:uppercase; margin-bottom:10px;\">Le Collaborateur</div><div style=\"border:2px solid {$cb}; height:90px; padding:8px; text-align:center; padding-top:30px;\"><strong>{{candidate_name}}</strong></div></td></tr></table></div>",
            'footer' => "<div style=\"margin-top:30px; border-top:2px solid {$c}; padding-top:12px; text-align:center; font-size:9px; color:{$c};\"><p>SyfonuRH Premium — Document Certifié — {{today}} — {{contract_id}}</p></div>"
        ];
    }

    private function templateMinimal(string $c, string $cb): array
    {
        return [
            'header' => "<div style=\"padding-bottom:15px; margin-bottom:20px; border-bottom:1px solid #e5e7eb;\"><h1 style=\"font-size:22px; color:#111; margin:0; font-weight:300; letter-spacing:1px;\">Contrat de Travail</h1><p style=\"color:#9ca3af; font-size:11px; margin:4px 0 0 0;\">{{contract_id}} · {{today}}</p></div>",
            'body' => "<div style=\"font-family:Arial,sans-serif; color:#374151; line-height:1.8;\"><p><strong style=\"color:{$c};\">Parties :</strong> Ce contrat lie <strong>{{recruiter_name}}</strong> et <strong>{{candidate_name}}</strong>.</p><p><strong style=\"color:{$c};\">Poste :</strong> {{job_title}} — À compter du {{start_date}}</p><p><strong style=\"color:{$c};\">Rémunération :</strong> {{salary}} TND brut mensuel</p><p><strong style=\"color:{$c};\">Obligations :</strong> Confidentialité et respect des dispositions légales en vigueur.</p><table style=\"width:100%; margin-top:50px; border-collapse:collapse;\"><tr><td style=\"width:50%; border-top:1px solid #d1d5db; padding-top:10px; font-size:11px; color:#9ca3af;\">Employeur — {{recruiter_name}}</td><td style=\"width:50%; border-top:1px solid #d1d5db; padding-top:10px; font-size:11px; color:#9ca3af; text-align:right;\">Collaborateur — {{candidate_name}}</td></tr></table></div>",
            'footer' => "<div style=\"margin-top:25px; padding-top:10px; border-top:1px solid #f3f4f6; font-size:9px; color:#d1d5db; text-align:center;\"><p>SyfonuRH · {{today}} · {{contract_id}}</p></div>"
        ];
    }

    private function templateBold(string $c, string $cl, string $cb): array
    {
        return [
            'header' => "<div style=\"background:#111; padding:25px; margin-bottom:25px;\"><h1 style=\"color:{$cb}; margin:0; font-size:32px; text-transform:uppercase; letter-spacing:3px;\">Contrat de Travail</h1><div style=\"color:#fff; font-size:12px; margin-top:8px; opacity:0.7;\">SyfonuRH · ID: {{contract_id}} · {{today}}</div></div>",
            'body' => "<div style=\"font-family:Arial,sans-serif; color:#1a1a1a; line-height:1.8;\"><div style=\"background:#111; color:#fff; padding:12px 20px; margin-bottom:15px;\"><strong style=\"font-size:13px; text-transform:uppercase; letter-spacing:1px;\">I. Parties</strong></div><p style=\"padding:0 5px;\">Employeur : <strong>{{recruiter_name}}</strong> — Collaborateur : <strong>{{candidate_name}}</strong></p><div style=\"background:#111; color:#fff; padding:12px 20px; margin:15px 0;\"><strong style=\"font-size:13px; text-transform:uppercase; letter-spacing:1px;\">II. Poste &amp; Salaire</strong></div><p style=\"padding:0 5px;\">Poste : <strong>{{job_title}}</strong> · Début : <strong>{{start_date}}</strong> · Brut mensuel : <strong>{{salary}} TND</strong></p><div style=\"background:#111; color:#fff; padding:12px 20px; margin:15px 0;\"><strong style=\"font-size:13px; text-transform:uppercase; letter-spacing:1px;\">III. Signatures</strong></div><table style=\"width:100%; margin-top:15px; border-collapse:collapse;\"><tr><td style=\"width:50%; padding-right:15px;\"><div style=\"background:{$cl}; border-left:4px solid {$cb}; height:90px; padding:10px;\"><div style=\"font-size:10px; color:#666; margin-bottom:5px;\">Employeur</div></div></td><td style=\"width:50%; padding-left:15px;\"><div style=\"border-left:4px solid {$cb}; height:90px; padding:10px;\"><div style=\"font-size:10px; color:#666; margin-bottom:5px;\">Collaborateur</div><div style=\"font-weight:bold; color:#111;\">{{candidate_name}}</div></div></td></tr></table></div>",
            'footer' => "<div style=\"margin-top:25px; background:#111; color:#666; padding:12px 20px; font-size:9px; text-align:center;\"><p>SyfonuRH © 2025 — Document Officiel — {{contract_id}} — {{today}}</p></div>"
        ];
    }

    private function templateModern(string $c, string $cl, string $cb): array
    {
        return [
            'header' => "<div style=\"background:linear-gradient(135deg,{$c},{$cb}); color:#fff; padding:28px; border-radius:8px; margin-bottom:25px;\"><h1 style=\"margin:0; font-size:26px; letter-spacing:2px;\">ACCORD PROFESSIONNEL</h1><p style=\"margin:6px 0 0 0; opacity:0.85; font-size:12px;\">SyfonuRH Digital · Réf: {{contract_id}} · Édité le {{today}}</p></div>",
            'body' => "<div style=\"font-family:Arial,sans-serif; color:#1f2937; line-height:1.8;\"><div style=\"background:{$cl}; border-radius:6px; padding:18px; margin-bottom:18px;\"><h3 style=\"color:{$c}; margin:0 0 10px 0; font-size:13px; text-transform:uppercase;\">Parties Contractantes</h3><p style=\"margin:0;\">Employeur : <strong>{{recruiter_name}}</strong><br>Collaborateur : <strong>{{candidate_name}}</strong></p></div><div style=\"background:#f9fafb; border-radius:6px; padding:18px; margin-bottom:18px;\"><h3 style=\"color:{$c}; margin:0 0 10px 0; font-size:13px; text-transform:uppercase;\">Mission &amp; Rémunération</h3><p style=\"margin:0;\">Titre : <strong>{{job_title}}</strong> · Début : <strong>{{start_date}}</strong><br>Rémunération brute mensuelle : <strong>{{salary}} TND</strong></p></div><table style=\"width:100%; margin-top:40px; border-collapse:collapse;\"><tr><td style=\"width:50%; padding-right:12px;\"><div style=\"border:2px solid {$cb}; border-radius:6px; height:90px; background:{$cl}; padding:10px;\"><div style=\"font-size:10px; color:{$c}; font-weight:bold; text-transform:uppercase;\">Employeur</div></div></td><td style=\"width:50%; padding-left:12px;\"><div style=\"border:2px solid {$cb}; border-radius:6px; height:90px; padding:10px;\"><div style=\"font-size:10px; color:{$c}; font-weight:bold; text-transform:uppercase;\">Collaborateur</div><div style=\"margin-top:20px; font-weight:bold;\">{{candidate_name}}</div></div></td></tr></table></div>",
            'footer' => "<div style=\"margin-top:25px; padding-top:12px; border-top:2px solid {$cb}; font-size:9px; color:#9ca3af; text-align:center;\"><p>Généré par SyfonuRH Digital Platform — {{today}} — {{contract_id}}</p></div>"
        ];
    }
}
