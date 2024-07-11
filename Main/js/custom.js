document.addEventListener("DOMContentLoaded", function () {
    // ---For Ads Modal---
    let modalTrigger = document.getElementById('modalTrigger');
    modalTrigger.click();

    // ---Owl carousel config for Latest Updates--- 
    const latestUpdatesCarousel = document.getElementById('latestUpdatesCarousel');
    $(latestUpdatesCarousel).owlCarousel({
        loop: true,
        margin: 10,
        nav: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        navText: ['<span class="owl-prev">&#10094;</span>', '<span class="owl-next">&#10095;</span>'],
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 3
            }
        }
    });
    
    document.querySelectorAll('.see-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const cardBody = btn.closest('.card-body');
            const shortDesc = cardBody.querySelector('.short-desc');
            const fullDesc = cardBody.querySelector('.full-desc');

            if (shortDesc && fullDesc) {
                shortDesc.classList.toggle('d-none');
                fullDesc.classList.toggle('d-none');
                btn.textContent = btn.textContent === 'See more' ? 'See less' : 'See more';
            }
        });
    });
});