document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('logoutBtn');
  if (!btn) return;
  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    const url = window.LOGOUT_URL || 'api/logout.php';
    try {
      await fetch(url, { method: 'POST' });
    } finally {
      location.href = window.LOGOUT_REDIRECT || '/';
    }
  });
});
