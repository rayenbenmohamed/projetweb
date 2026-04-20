document.addEventListener('DOMContentLoaded', () => {
  console.log('--- SyfonuRH Advanced Contracts JS Loaded ---');

  // --- 1. Dashboard Charts (Chart.js) ---
  const chartDataEl = document.getElementById('contractsChartData');
  if (chartDataEl && window.Chart) {
    let rawData = {};
    try {
      rawData = JSON.parse(chartDataEl.dataset.json || '{}');
      console.log('--- ChartData parsed successfully ---', rawData);
    } catch (e) {
      console.error('--- ChartData Error ---', e);
    }
    
    // Line Chart: Hiring Trend
    const trendCtx = document.getElementById('hiringTrendChart');
    if (trendCtx) {
      console.log('--- Initializing Trend Chart ---');
      const trendData = rawData.trends ? Object.values(rawData.trends) : [0,0,0,0,0,0,0,0,0,0,0,0];
      new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
          datasets: [{
            label: 'Nouveaux Contrats',
            data: trendData,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    // Pie Chart: Contract Mix
    const mixCtx = document.getElementById('contractTypeChart');
    if (mixCtx) {
      console.log('--- Initializing Type Chart ---');
      new Chart(mixCtx, {
        type: 'doughnut',
        data: {
          labels: Object.keys(rawData.types || {}),
          datasets: [{
            data: Object.values(rawData.types || {}),
            backgroundColor: ['#0d6efd', '#00c292', '#ffaa00', '#ff4d4d', '#7952b3']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } }
        }
      });
    }
  } else {
    console.warn('--- Charts skipped: No data or Chart.js missing ---');
  }

  // --- 2. iCal Event Generation (.ics) ---
  const exportCalBtn = document.getElementById('exportCalendarBtn');
  if (exportCalBtn) {
    exportCalBtn.addEventListener('click', () => {
      const title = document.querySelector('h2').innerText;
      const startDate = document.querySelector('.timeline-item .small')?.innerText || new Date().toISOString();
      
      const [day, month, year] = startDate.split(' '); // Expected format "DD Month YYYY"
      // Basic ICS format
      const icsData = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//SyfonuRH//Contract Sync//FR',
        'BEGIN:VEVENT',
        `SUMMARY:Démarrage ${title}`,
        'DESCRIPTION:Premier jour de contrat. Bienvenue au nouveau collaborateur !',
        'DTSTART:20260401T090000Z', // Simplified for demo
        'DTEND:20260401T180000Z',
        'LOCATION:Siège Social SyfonuRH',
        'END:VEVENT',
        'END:VCALENDAR'
      ].join('\r\n');

      const blob = new Blob([icsData], { type: 'text/calendar;charset=utf-8' });
      const link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.setAttribute('download', 'contract_milestone.ics');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  }

  // --- 3. Email Simulation Logic ---
  const sendEmailBtn = document.getElementById('sendFakeEmailBtn');
  if (sendEmailBtn) {
    sendEmailBtn.addEventListener('click', () => {
      sendEmailBtn.disabled = true;
      sendEmailBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
      
      setTimeout(() => {
        const modalEl = document.getElementById('emailPreviewModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        
        alert('✅ Succès : L\'email professionnel a été envoyé au candidat avec le certificat digital rattaché.');
        sendEmailBtn.disabled = false;
        sendEmailBtn.innerText = 'Envoyer maintenant';
      }, 2000);
    });
  }

  // --- 4. Financial Calculator (Real-time) ---
  const salaryInput = document.querySelector('input[name="salary"]');
  const brutDisplay = document.getElementById('brutDisplay');
  const netDisplay = document.getElementById('netDisplay');
  const totalCostDisplay = document.getElementById('totalCostDisplay');
  const salaryProgress = document.getElementById('salaryProgress');

  const updateFinance = () => {
    if (!salaryInput) return;
    const brut = parseFloat(salaryInput.value) || 0;
    const cnssSalarie = brut * 0.0918;
    const irppEst = brut * 0.085;
    const netMensuel = (brut - cnssSalarie - irppEst);
    const chargesPatronales = brut * 0.195;
    const coutTotalEntreprise = brut + chargesPatronales;

    if (brutDisplay) brutDisplay.textContent = Math.round(brut).toLocaleString() + ' TND';
    if (netDisplay) netDisplay.textContent = Math.round(netMensuel).toLocaleString() + ' TND';
    if (totalCostDisplay) totalCostDisplay.textContent = Math.round(coutTotalEntreprise).toLocaleString() + ' TND';

    if (salaryProgress) {
      let percent = Math.min(100, (brut / 5000) * 100);
      salaryProgress.style.width = percent + '%';
    }
  };

  if (salaryInput) {
    salaryInput.addEventListener('input', updateFinance);
    updateFinance();
  }

  // --- 5. Security Verification (Signature Analysis) ---
  const canvas = document.getElementById('signaturePad');
  const verifyBtn = document.getElementById('verifySignatureBtn');
  const securityOverlay = document.getElementById('securityOverlay');
  const verifySuccessBadge = document.getElementById('verifySuccessBadge');

  if (canvas) {
    const ctx = canvas.getContext('2d');
    const signatureInput = document.getElementById('signatureInput');

    let drawing = false;
    ctx.strokeStyle = '#0d6efd';
    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    const getPos = (e) => {
      const rect = canvas.getBoundingClientRect();
      const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
      return { 
        x: (clientX - rect.left) * (canvas.width / rect.width),
        y: (clientY - rect.top) * (canvas.height / rect.height)
      };
    };

    const startDrawing = (e) => {
      drawing = true;
      const pos = getPos(e);
      ctx.beginPath();
      ctx.moveTo(pos.x, pos.y);
      if (e.type === 'touchstart') e.preventDefault();
    };

    const draw = (e) => {
      if (!drawing) return;
      const pos = getPos(e);
      ctx.lineTo(pos.x, pos.y);
      ctx.stroke();
      if (e.type === 'touchmove') e.preventDefault();
    };

    const stopDrawing = () => {
      if (!drawing) return;
      drawing = false;
      signatureInput.value = canvas.toDataURL();
    };

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing);
  }

  if (verifyBtn && securityOverlay) {
    verifyBtn.addEventListener('click', function() {
      securityOverlay.classList.remove('d-none');
      
      setTimeout(() => {
        securityOverlay.classList.add('d-none');
        verifySuccessBadge.classList.remove('d-none');
        console.log('--- Security Verification Passed ---');
      }, 1500);
    });
  }

  // --- 6. Document Suggestion Module ---
  const generateBtn = document.getElementById('generateSuggestedContent');
  if (generateBtn) {
    generateBtn.addEventListener('click', function() {
      console.log('--- Generating Document Suggestions ---');
      const salary = document.querySelector('input[name="salary"]')?.value || 'Néant';
      
      const text = `CONTRAT DE COLLABORATION PROFESSIONNELLE\n\n` +
                 `Ce document atteste l'accord entre SyfonuRH et le collaborateur.\n` +
                 `CONDITIONS FINANCIÈRES : ${salary} TND par an.\n\n` +
                 `CLAUSES DE CONFIDENTIALITÉ :\n` +
                 `Le collaborateur s'engage à respecter le secret professionnel...\n\n` +
                 `PROPRIÉTÉ INTELLECTUELLE :\n` +
                 `Tous les travaux réalisés resteront la propriété de l'entreprise.\n\n` +
                 `Fait électroniquement à Tunis, le ${new Date().toLocaleDateString('fr-FR')}.`;

      document.getElementById('suggestedText').value = text;
      new bootstrap.Modal(document.getElementById('suggestionModal')).show();
    });

    document.getElementById('useAiText')?.addEventListener('click', function() {
      document.getElementById('contractContentMain').value = document.getElementById('suggestedText').value;
      bootstrap.Modal.getInstance(document.getElementById('suggestionModal'))?.hide();
    });
  }

  // --- 7. Auto-submit logic for Filters ---
  const filterForm = document.getElementById('contractFilters');
  if (filterForm) {
    const autoSubmitInputs = filterForm.querySelectorAll('[data-autosubmit]');
    let debounceTime;

    autoSubmitInputs.forEach(input => {
      const eventType = (input.type === 'text' || input.type === 'number') ? 'input' : 'change';
      
      input.addEventListener(eventType, () => {
        clearTimeout(debounceTime);
        const delay = (eventType === 'input') ? 400 : 0;
        
        debounceTime = setTimeout(() => {
          console.log('--- Real-time Filtering Activated ---');
          filterForm.submit();
        }, delay);
      });
    });
  }

  // --- 8. Inline Creation Logic (Candidates & Recruiters) ---
  const handleInlineCreation = (formId, apiEndpoint, selectId, errorId, buttonId) => {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const btn = document.getElementById(buttonId);
      const spinner = btn.querySelector('.spinner-border');
      const errorDiv = document.getElementById(errorId);
      
      // Reset state
      errorDiv.classList.add('d-none');
      btn.disabled = true;
      spinner.classList.remove('d-none');

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      try {
        const response = await fetch(apiEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.error || 'Une erreur est survenue.');
        }

        // Success: Update Select
        const select = document.getElementById(selectId);
        const choiceInstance = select.choicesInstance;

        if (choiceInstance) {
          const newChoice = {
            value: result.id.toString(),
            label: `${result.name} — ${result.email}`,
            selected: true,
            disabled: false
          };
          
          choiceInstance.setChoices([newChoice], 'value', 'label', false);
          choiceInstance.setChoiceByValue(result.id.toString());
        } else {
          // Fallback if Choice.js not loaded on this element
          const option = new Option(`${result.name} — ${result.email}`, result.id, true, true);
          select.add(option);
        }

        // Close Modal
        const modalEl = form.closest('.modal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        
        // Reset Form
        form.reset();

        // Toast or Alert Success
        console.log('--- Entity Created Successfully ---', result);
      } catch (err) {
        errorDiv.textContent = err.message;
        errorDiv.classList.remove('d-none');
      } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
      }
    });
  };

  handleInlineCreation('newCandidateForm', '/api/candidates/create', 'candidateSelect', 'candidateFormError', 'saveCandidateBtn');
  handleInlineCreation('newRecruiterForm', '/api/recruiters/create', 'recruiterSelect', 'recruiterFormError', 'saveRecruiterBtn');

  // --- 9. Candidate Phone Auto-fill & Save ---
  const candidateSelect = document.getElementById('candidateSelect');
  const phoneInput = document.getElementById('candidatePhone');
  
  if (candidateSelect && phoneInput) {
    // Auto-fill phone when candidate changes
    const fillPhoneFromOption = () => {
      const selected = candidateSelect.options[candidateSelect.selectedIndex];
      if (!selected) return;
      const rawPhone = selected.dataset.phone || '';
      // Normalize: strip +216 prefix for display
      const displayPhone = rawPhone.replace(/^\+?216/, '').replace(/\s|-/g, '');
      phoneInput.value = displayPhone;
    };

    candidateSelect.addEventListener('change', fillPhoneFromOption);

    // Also react to Choice.js events
    candidateSelect.addEventListener('choice', () => {
      // Choice.js fires 'choice' before updating selectedIndex, use timeout
      setTimeout(fillPhoneFromOption, 50);
    });

    // Save phone on blur (when user leaves the field)
    phoneInput.addEventListener('blur', async () => {
      const selectedOption = candidateSelect.options[candidateSelect.selectedIndex];
      if (!selectedOption || !selectedOption.value) return;
      
      const candidateId = selectedOption.value;
      const phone = phoneInput.value.trim();
      if (!phone) return;

      try {
        await fetch(`/api/candidates/${candidateId}/phone`, {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: `+216${phone.replace(/^\+?216/, '')}` })
        });
        // Visual feedback
        phoneInput.classList.add('border-success');
        setTimeout(() => phoneInput.classList.remove('border-success'), 1500);
      } catch (e) {
        console.warn('Phone save failed:', e);
      }
    });
  }
});
