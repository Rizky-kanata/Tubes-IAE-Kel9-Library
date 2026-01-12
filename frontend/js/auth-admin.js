const loginAdminForm = document.getElementById('loginForm');
if (loginAdminForm) {
    loginAdminForm.addEventListener('submit', async function(e) {
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
                
                if (userData.user && userData.user.role !== 'admin') {
                    showMessage('❌ Akses ditolak! Gunakan login member.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Login sebagai Admin';
                    return;
                }

                setToken(userData.access_token || userData.token);
                localStorage.setItem('user', JSON.stringify(userData.user));
                localStorage.setItem('role', userData.user.role);
                
                showMessage('✅ Login admin berhasil!', 'success');
                
                setTimeout(() => {
                    window.location.href = 'admin-dashboard.html';
                }, 1500);
            } else {
                showMessage('❌ ' + result.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login sebagai Admin';
            }
        } catch (error) {
            showMessage('❌ Error: ' + error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login sebagai Admin';
        }
    });
}
