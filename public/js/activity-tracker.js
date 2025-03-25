// Fungsi untuk mengirim heartbeat ke server setiap menit
function setupHeartbeat() {
    // Ambil token dari localStorage atau tempat penyimpanan lainnya
    const token = localStorage.getItem('auth_token');
    
    if (!token) return; // Jika tidak ada token, tidak perlu melakukan heartbeat
    
    // Kirim heartbeat setiap 1 menit
    setInterval(() => {
        fetch('/activity/heartbeat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .catch(error => console.error('Heartbeat error:', error));
    }, 60000); // 60000 ms = 1 menit
}

// Fungsi untuk mendeteksi idle dan melakukan auto-logout
function setupIdleDetection() {
    const token = localStorage.getItem('auth_token');
    if (!token) return;
    
    let idleTime = 0;
    const idleLimit = 30; // Auto-logout setelah 30 menit tidak aktif
    
    // Increment idleTime setiap menit
    const idleInterval = setInterval(() => {
        idleTime++;
        
        // Jika idle time melebihi batas, lakukan logout
        if (idleTime >= idleLimit) {
            clearInterval(idleInterval);
            logout();
        }
    }, 60000);
    
    // Reset idleTime saat ada aktivitas
    function resetIdleTime() {
        idleTime = 0;
    }
    
    // Fungsi untuk melakukan logout
    function logout() {
        fetch('/auth/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            localStorage.removeItem('auth_token');
            window.location.href = '/login'; // Redirect ke halaman login
        })
        .catch(error => console.error('Logout error:', error));
    }
    
    // Tambahkan event listener untuk reset idle time
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    events.forEach(event => {
        document.addEventListener(event, resetIdleTime, true);
    });
}

// Panggil fungsi ini saat aplikasi dimuat
document.addEventListener('DOMContentLoaded', () => {
    setupHeartbeat();
    setupIdleDetection();
}); 