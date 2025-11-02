// System oceniania zdjęć
document.addEventListener('DOMContentLoaded', function() {
    const starsInputs = document.querySelectorAll('.stars-input');

    starsInputs.forEach(container => {
        const stars = container.querySelectorAll('.star-input');
        const photoId = container.dataset.photoId;

        // Hover effect
        stars.forEach((star, index) => {
            star.addEventListener('mouseenter', function() {
                highlightStars(stars, index + 1);
            });

            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                submitRating(photoId, rating, container);
            });
        });

        container.addEventListener('mouseleave', function() {
            resetStars(stars);
        });
    });
});

function highlightStars(stars, count) {
    stars.forEach((star, index) => {
        if (index < count) {
            star.textContent = '★';
            star.classList.add('hover');
        } else {
            star.textContent = '☆';
            star.classList.remove('hover');
        }
    });
}

function resetStars(stars) {
    stars.forEach(star => {
        star.textContent = '☆';
        star.classList.remove('hover');
    });
}

function submitRating(photoId, rating, container) {
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value ||
        document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) {
        alert('Błąd: brak tokenu CSRF');
        return;
    }

    const formData = new FormData();
    formData.append('photo_id', photoId);
    formData.append('rating', rating);
    formData.append('csrf_token', csrfToken);

    fetch('/photo_game/actions/rate_photo.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePhotoRating(photoId, rating, data.avg_rating, data.rating_count);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Błąd:', error);
            alert('Wystąpił błąd podczas oceniania');
        });
}

function updatePhotoRating(photoId, userRating, avgRating, ratingCount) {
    const galleryItem = document.querySelector(`.gallery-item[data-photo-id="${photoId}"]`);
    if (!galleryItem) return;

    // Usuń formularz oceniania
    const ratingInput = galleryItem.querySelector('.photo-rating-input');
    if (ratingInput) {
        ratingInput.remove();
    }

    // Zaktualizuj wyświetlanie średniej oceny
    const ratingDisplay = galleryItem.querySelector('.photo-rating-display');
    if (ratingDisplay) {
        const fullStars = Math.floor(avgRating);
        const hasHalf = (avgRating - fullStars) >= 0.5;

        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= fullStars) {
                starsHtml += '<span class="star filled">★</span>';
            } else if (i == fullStars + 1 && hasHalf) {
                starsHtml += '<span class="star half">★</span>';
            } else {
                starsHtml += '<span class="star">☆</span>';
            }
        }

        const ocenyText = ratingCount == 1 ? 'ocena' :
            ratingCount < 5 ? 'oceny' : 'ocen';

        ratingDisplay.querySelector('.stars-display').innerHTML = starsHtml;
        ratingDisplay.querySelector('.rating-text').textContent =
            `${avgRating.toFixed(1)} (${ratingCount} ${ocenyText})`;
    }

    // Dodaj informację o ocenie użytkownika
    const userRatingInfo = document.createElement('div');
    userRatingInfo.className = 'user-rating-info';
    userRatingInfo.innerHTML = 'Twoja ocena: ' + '★'.repeat(userRating) + '☆'.repeat(5 - userRating);

    const description = galleryItem.querySelector('ratings  ');
    if (description) {
        description.parentNode.insertBefore(userRatingInfo, description);
    }
}