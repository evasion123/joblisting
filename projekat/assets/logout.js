document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('logoutBtn');
  if (!btn) return;
  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    const url = window.LOGOUT_URL || 'api/logout.php';
    try {
      await fetch(url, { method: 'POST' });
    } finally {
      location.href = window.LOGOUT_REDIRECT || 'https://studcp.vts.su.ac.rs:10000/virtual-server/link.cgi/147.91.199.133/http://www.evasion.stud.vts.su.ac.rs/';
    }
  });
});
