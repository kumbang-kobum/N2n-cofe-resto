<script>
  (() => {
    const savedTheme = localStorage.getItem('n2n-theme') || 'light';
    document.documentElement.classList.remove('theme-dark', 'theme-forest', 'theme-amber', 'theme-maroon');
    if (savedTheme === 'dark') document.documentElement.classList.add('theme-dark');
    if (savedTheme === 'forest') document.documentElement.classList.add('theme-forest');
    if (savedTheme === 'amber') document.documentElement.classList.add('theme-amber');
    if (savedTheme === 'maroon') document.documentElement.classList.add('theme-maroon');
  })();
</script>
