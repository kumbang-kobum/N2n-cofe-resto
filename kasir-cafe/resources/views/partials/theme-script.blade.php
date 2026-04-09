<script>
  (() => {
    const selects = document.querySelectorAll('[data-theme-select]');
    if (!selects.length) return;

    const applyTheme = (theme) => {
      document.documentElement.classList.remove('theme-dark', 'theme-forest', 'theme-amber', 'theme-maroon');
      if (theme === 'dark') document.documentElement.classList.add('theme-dark');
      if (theme === 'forest') document.documentElement.classList.add('theme-forest');
      if (theme === 'amber') document.documentElement.classList.add('theme-amber');
      if (theme === 'maroon') document.documentElement.classList.add('theme-maroon');
      localStorage.setItem('n2n-theme', theme);
      selects.forEach((select) => {
        select.value = theme;
      });
    };

    const savedTheme = localStorage.getItem('n2n-theme') || 'light';
    selects.forEach((select) => {
      select.value = savedTheme;
      select.addEventListener('change', (event) => {
        applyTheme(event.target.value);
      });
    });

    applyTheme(savedTheme);
  })();
</script>
