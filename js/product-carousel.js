class ProductCarousel {
    constructor() {
        this.container = document.querySelector('.products-carousel-container');
        this.track = document.querySelector('.carousel-track');
        this.cards = Array.from(document.querySelectorAll('.product-card'));
        this.prevButton = document.querySelector('.carousel-prev');
        this.nextButton = document.querySelector('.carousel-next');
        this.modal = document.getElementById('product-modal');
        this.modalClose = document.querySelector('.modal-close');
        this.modalPrev = document.querySelector('.modal-prev');
        this.modalNext = document.querySelector('.modal-next');
        this.currentIndex = 0;
        this.isDragging = false;
        this.startPos = 0;
        this.currentTranslate = 0;
        this.prevTranslate = 0;
        this.cardWidth = 0;
        this.cardsPerView = 3; // Number of cards visible at once
        this.gap = 30; // Gap between cards in pixels

        this.init();
    }

    init() {
        if (!this.container || !this.track) return;

        // Calculate card width based on container width
        this.updateCardWidth();

        // Set up event listeners
        this.prevButton?.addEventListener('click', () => this.slide('prev'));
        this.nextButton?.addEventListener('click', () => this.slide('next'));
        this.modalClose?.addEventListener('click', () => this.closeModal());
        this.modalPrev?.addEventListener('click', () => this.modalSlide('prev'));
        this.modalNext?.addEventListener('click', () => this.modalSlide('next'));

        // Touch events for mobile
        this.track.addEventListener('touchstart', this.touchStart.bind(this));
        this.track.addEventListener('touchmove', this.touchMove.bind(this));
        this.track.addEventListener('touchend', this.touchEnd.bind(this));

        // Keyboard navigation
        this.track.addEventListener('keydown', this.handleKeyDown.bind(this));

        // Product card click events
        this.cards.forEach((card, index) => {
            card.addEventListener('click', () => this.openModal(index));
        });

        // Close modal on overlay click
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal?.classList.contains('active')) {
                this.closeModal();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            this.updateCardWidth();
            this.updateButtonStates();
        });

        // Add cart button click handlers
        this.cards.forEach((card) => {
            const addToCartBtn = card.querySelector('.add-to-cart-btn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleAddToCart(addToCartBtn);
                });
            }
        });

        // Initial setup
        this.updateButtonStates();
    }

    updateCardWidth() {
        const containerWidth = this.container.offsetWidth;
        this.cardWidth = (containerWidth - (this.gap * (this.cardsPerView - 1))) / this.cardsPerView;
        
        // Update card widths
        this.cards.forEach(card => {
            card.style.width = `${this.cardWidth}px`;
        });
    }

    slide(direction) {
        const scrollAmount = this.cardWidth + this.gap;
        
        if (direction === 'prev') {
            this.track.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        } else {
            this.track.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }

        // Update button states after animation
        setTimeout(() => this.updateButtonStates(), 300);
    }

    updateButtonStates() {
        if (!this.prevButton || !this.nextButton) return;

        const containerWidth = this.container.offsetWidth;
        const maxScroll = this.track.scrollWidth - containerWidth;
        
        // Update prev button
        this.prevButton.style.opacity = this.track.scrollLeft <= 0 ? '0.5' : '1';
        this.prevButton.disabled = this.track.scrollLeft <= 0;
        
        // Update next button
        this.nextButton.style.opacity = this.track.scrollLeft >= maxScroll ? '0.5' : '1';
        this.nextButton.disabled = this.track.scrollLeft >= maxScroll;
    }

    touchStart(e) {
        this.isDragging = true;
        this.startPos = e.touches[0].clientX;
        this.prevTranslate = this.track.scrollLeft;
    }

    touchMove(e) {
        if (!this.isDragging) return;
        
        const currentPosition = e.touches[0].clientX;
        const diff = currentPosition - this.startPos;
        this.track.scrollLeft = this.prevTranslate - diff;
    }

    touchEnd() {
        this.isDragging = false;
        this.updateButtonStates();
    }

    handleKeyDown(e) {
        if (e.key === 'ArrowLeft') {
            this.slide('prev');
        } else if (e.key === 'ArrowRight') {
            this.slide('next');
        }
    }

    openModal(index) {
        if (!this.modal || !this.cards[index]) return;

        this.currentIndex = index;
        const card = this.cards[index];
        
        // Update modal content
        const modalImage = this.modal.querySelector('#modal-product-image');
        const modalTitle = this.modal.querySelector('#modal-product-title');
        const modalPrice = this.modal.querySelector('#modal-product-price');
        const modalDescription = this.modal.querySelector('#modal-product-description');
        const modalLink = this.modal.querySelector('#modal-product-link');

        if (modalImage) modalImage.src = card.querySelector('img').src;
        if (modalTitle) modalTitle.textContent = card.querySelector('.product-title').textContent;
        if (modalPrice) modalPrice.innerHTML = card.querySelector('.product-price').innerHTML;
        if (modalDescription) modalDescription.textContent = card.querySelector('.product-description').textContent;
        if (modalLink) modalLink.href = card.querySelector('.quick-view-btn').href;

        // Show modal
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Update modal navigation states
        this.updateModalNavigation();
    }

    closeModal() {
        if (!this.modal) return;

        this.modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    modalSlide(direction) {
        if (direction === 'prev' && this.currentIndex > 0) {
            this.currentIndex--;
        } else if (direction === 'next' && this.currentIndex < this.cards.length - 1) {
            this.currentIndex++;
        }

        this.openModal(this.currentIndex);
    }

    updateModalNavigation() {
        if (!this.modalPrev || !this.modalNext) return;

        this.modalPrev.style.opacity = this.currentIndex === 0 ? '0.5' : '1';
        this.modalNext.style.opacity = this.currentIndex === this.cards.length - 1 ? '0.5' : '1';
    }

    handleAddToCart(button) {
        const productId = button.dataset.productId;
        if (!productId) return;

        // Show loading state
        button.classList.add('loading');
        const originalText = button.textContent;
        button.textContent = 'Adding...';

        // Make AJAX request
        fetch(cbd_beauty_ajax.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'cbd_beauty_add_to_cart',
                nonce: cbd_beauty_ajax.nonce,
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count and total
                if (data.data.cart_count !== undefined) {
                    document.querySelectorAll('.cart-count').forEach(el => {
                        el.textContent = data.data.cart_count;
                    });
                }
                if (data.data.cart_total !== undefined) {
                    document.querySelectorAll('.cart-total-amount').forEach(el => {
                        el.textContent = data.data.cart_total;
                    });
                }
                // Show success message
                this.showMessage('Product added to cart successfully!', 'success');
            } else {
                this.showMessage(data.data.message || 'Failed to add product to cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showMessage('An error occurred while adding to cart', 'error');
        })
        .finally(() => {
            // Reset button state
            button.classList.remove('loading');
            button.textContent = originalText;
        });
    }

    showMessage(message, type = 'success') {
        const messageEl = document.createElement('div');
        messageEl.className = `cart-message ${type}`;
        messageEl.textContent = message;
        document.body.appendChild(messageEl);

        // Show message
        setTimeout(() => messageEl.classList.add('show'), 100);

        // Remove message after 3 seconds
        setTimeout(() => {
            messageEl.classList.remove('show');
            setTimeout(() => messageEl.remove(), 300);
        }, 3000);
    }
}

// Initialize the carousel when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProductCarousel();
});
