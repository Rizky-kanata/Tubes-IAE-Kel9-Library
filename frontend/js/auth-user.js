const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Loading...';

        const data = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        try {
            const result = await apiRequest('/login', {
                method: 'POST',
                body: JSON.stringify(data),
                noAuth: true
            });

            if (result.success) {
                const userData = result.data || result;
                
                if (userData.user && userData.user.role !== 'member') {
                    showMessage('❌ Akses ditolak! Gunakan login admin.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login sebagai Member';
                    return;
                }

                setToken(userData.access_token || userData.token);
                localStorage.setItem('user', JSON.stringify(userData.user));
                localStorage.setItem('role', userData.user.role);
                
                showMessage('✅ Login berhasil!', 'success');
                
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                showMessage('❌ ' + result.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login sebagai Member';
            }
        } catch (error) {
            showMessage('❌ Error: ' + error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login sebagai Member';
        }
    });
}
