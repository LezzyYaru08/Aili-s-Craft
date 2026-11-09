const AUTH_KEY = 'ac_new_admin_auth_v1';
const CURRENT_KEY = 'ac_new_admin_current_v1';
// A localStorage flag is not proof of identity. Require a successful database login.
localStorage.removeItem(AUTH_KEY);
document.getElementById('loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const identity = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const error = document.getElementById('loginError');
  const button = e.currentTarget.querySelector('[type="submit"]');
  error.textContent = ''; if(button) button.disabled = true;
  try {
    const response = await fetch('../AilisCraftUser/api/auth.php?action=staff-login', {method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body:JSON.stringify({username:identity,password,panel:'admin'})});
    const data = await response.json();
    if(!response.ok || !data.ok) throw new Error(data.error || 'Incorrect administrator credentials.');
    localStorage.setItem(AUTH_KEY, 'true');
    localStorage.setItem(CURRENT_KEY, String(data.staff.id));
    localStorage.setItem('ac_new_admin_profile_v1', JSON.stringify(data.staff));
    localStorage.setItem('ac_new_admin_last_login_v1', new Date().toISOString());
    location.replace('index.php');
  } catch(err) { error.textContent = err.message || 'Unable to verify administrator account. Check database connection.'; }
  finally { if(button) button.disabled = false; }
});
