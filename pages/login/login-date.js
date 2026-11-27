(() => {
    const loginDate = document.getElementById('login-date');
    if (!loginDate) return;

    const statusElement = loginDate.parentElement || loginDate;

    fetch('../login/login_history.php', { cache: 'no-store' })
        .then(async response => {
            const result = await response.json();
            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Could not load login history.');
            }
            return result;
        })
        .then(result => {
            if (result.has_previous_login) {
                loginDate.textContent = result.label;
            } else {
                statusElement.textContent = result.message;
            }
        })
        .catch(error => {
            console.error('Could not load login history:', error);
            statusElement.textContent = 'Login history unavailable.';
        });
})();
