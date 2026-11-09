const AUTH_KEY = "ailisCraftOwnerAuth";
// Do not trust a browser-only flag to authenticate the owner. Validate against MySQL every time.
localStorage.removeItem(AUTH_KEY);
document.getElementById("loginForm").addEventListener("submit", async function (event) {
  event.preventDefault();
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;
  const error = document.getElementById("loginError");
  const button = event.currentTarget.querySelector('[type="submit"]');
  error.textContent = ""; if (button) button.disabled = true;
  try {
    const response = await fetch("../AilisCraftUser/api/auth.php?action=staff-login", {method:"POST", headers:{"Content-Type":"application/json"}, credentials:"same-origin", body:JSON.stringify({username, password, panel:"owner"})});
    const data = await response.json();
    if (!response.ok || !data.ok) throw new Error(data.error || "Incorrect username or password.");
    localStorage.setItem(AUTH_KEY, "true");
    localStorage.setItem("ailisCraftOwnerProfile", JSON.stringify(data.staff));
    window.location.replace("index.php");
  } catch (err) { error.textContent = err.message || "Unable to verify owner account. Check database connection."; document.getElementById("password").select(); }
  finally { if (button) button.disabled = false; }
});
