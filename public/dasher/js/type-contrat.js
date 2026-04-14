document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('typeContratFilters');
  if (!form) return;

  let timer = null;
  const input = form.querySelector('[data-autosubmit]');
  if (!input) return;

  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => form.submit(), 350);
  });
});

