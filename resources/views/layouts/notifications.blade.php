<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="notificationToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i id="toast-icon" class="fas fa-info-circle me-2"></i>
                <span id="toast-message">Notification message</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Notification Sound -->
<audio id="notificationSound" preload="auto">
    <source src="{{ asset('sounds/notification.mp3') }}" type="audio/mpeg">
</audio>

<script>
    // Notification system
    const notificationToast = document.getElementById('notificationToast');
    const toast = new bootstrap.Toast(notificationToast, { delay: 5000 });
    const toastIcon = document.getElementById('toast-icon');
    const toastMessage = document.getElementById('toast-message');
    const notificationSound = document.getElementById('notificationSound');

    // Function to show a notification
    function showNotification(message, type = 'info', playSound = true) {
        // Set the toast background color based on type
        notificationToast.className = 'toast align-items-center text-white border-0';

        switch (type) {
            case 'success':
                notificationToast.classList.add('bg-success');
                toastIcon.className = 'fas fa-check-circle me-2';
                break;
            case 'warning':
                notificationToast.classList.add('bg-warning');
                toastIcon.className = 'fas fa-exclamation-triangle me-2';
                break;
            case 'error':
                notificationToast.classList.add('bg-danger');
                toastIcon.className = 'fas fa-times-circle me-2';
                break;
            case 'info':
            default:
                notificationToast.classList.add('bg-info');
                toastIcon.className = 'fas fa-info-circle me-2';
                break;
        }

        // Set message
        toastMessage.textContent = message;

        // Play sound if enabled
        if (playSound) {
            notificationSound.play().catch(e => console.log('Audio play failed:', e));
        }

        // Show the toast
        toast.show();
    }

    // Listen for notification events (example for Laravel Echo/Pusher)
    // This assumes you've set up Laravel Echo with Pusher in your project
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Echo !== 'undefined') {
            // Private channel for authenticated user notifications
            Echo.private('App.Models.User.' + userId)
                .notification((notification) => {
                    let message = '';
                    let type = 'info';

                    // Handle different notification types
                    switch (notification.type) {
                        case 'App\\Notifications\\InstructionAssigned':
                            message = 'New instruction assigned: ' + notification.title;
                            break;
                        case 'App\\Notifications\\InstructionReplied':
                            message = notification.replier_name + ' replied to an instruction';
                            break;
                        case 'App\\Notifications\\InstructionForwarded':
                            message = notification.forwarder_name + ' forwarded an instruction to you';
                            break;
                        default:
                            message = 'New notification received';
                    }

                    showNotification(message, type);

                    // Update notification badge count
                    updateNotificationBadge();
                });
        }
    });

    // Function to update notification badge count
    function updateNotificationBadge() {
        fetch('/api/notifications/count')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }

    // Initial badge update
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationBadge();
    });
</script>
