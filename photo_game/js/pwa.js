// Rejestracja Service Workera z obsługą aktualizacji
if ('serviceWorker' in navigator) {
    let refreshing = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (refreshing) return;
        refreshing = true;
        window.location.reload();
    });

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/photo_game/sw.js')
            .then(registration => {
                console.log('Service Worker zarejestrowany');

                // Sprawdzaj aktualizacje co 60 sekund
                setInterval(() => {
                    registration.update();
                }, 60000);

                // Obsługa aktualizacji
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // Nowa wersja dostępna
                            const banner = document.getElementById('update-banner');
                            if (banner) {
                                banner.style.display = 'block';
                            }
                        }
                    });
                });
            })
            .catch(error => {
                console.log('Błąd rejestracji SW:', error);
            });
    });
}