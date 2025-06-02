class IngredientCarousel {
    constructor(container) {
        this.container = container;
        this.track = container.querySelector('.carousel-track');
        this.cards = container.querySelectorAll('.ingredient-card');
        this.prevBtn = container.querySelector('.carousel-prev');
        this.nextBtn = container.querySelector('.carousel-next');
        this.modal = document.getElementById('ingredient-modal');
        this.currentIndex = 0;
        this.cardsPerView = this.getCardsPerView();
        this.maxIndex = Math.max(0, this.cards.length - this.cardsPerView);
        
        // Get ingredients data
        const productId = container.dataset.productId;
        const dataScript = document.getElementById(`ingredients-data-${productId}`);
        this.ingredientsData = dataScript ? JSON.parse(dataScript.textContent) : [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateCarousel();
        this.updateButtons();
        
        // Handle resize
        window.addEventListener('resize', () => {
            this.cardsPerView = this.getCardsPerView();
            this.maxIndex = Math.max(0, this.cards.length - this.cardsPerView);
            this.currentIndex = Math.min(this.currentIndex, this.maxIndex);
            this.updateCarousel();
            this.updateButtons();
        });
    }
    
    getCardsPerView() {
        return window.innerWidth <= 768 ? 1 : 3;
    }
    
    setupEventListeners() {
        // Carousel navigation
        this.prevBtn?.addEventListener('click', () => this.prev());
        this.nextBtn?.addEventListener('click', () => this.next());
        
        // Card clicks
        this.cards.forEach((card, index) => {
            card.addEventListener('click', (e) => {
                // Don't open modal if clicking the Add to Cart button
                if (!e.target.closest('.add-to-cart-btn')) {
                    this.openModal(index);
                }
            });
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (document.activeElement.classList.contains('add-to-cart-btn')) {
                        this.handleAddToCart(e.target);
                    } else {
                        this.openModal(index);
                    }
                }
            });
        });
        
        // Add to Cart buttons
        this.container.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleAddToCart(btn);
            });
        });
        
        // Modal events
        if (this.modal) {
            this.setupModalEvents();
        }
        
        // Touch/swipe support for mobile
        if (window.innerWidth <= 768) {
            this.setupTouchEvents();
        }
    }
    
    handleAddToCart(button) {
        const productId = button.dataset.productId;
        if (!productId) return;
        
        // Add loading state
        button.classList.add('loading');
        const originalText = button.textContent;
        button.textContent = '';
        
        // Use WooCommerce's built-in AJAX add to cart
        jQuery.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
            data: {
                product_id: productId,
                quantity: 1
            },
            success: (response) => {
                if (response.fragments) {
                    // Update cart fragments
                    jQuery.each(response.fragments, (key, value) => {
                        jQuery(key).replaceWith(value);
                    });
                }
                
                // Trigger WooCommerce events
                jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                
                // Show success message
                const message = response.message || 'Product added to cart!';
                this.showMessage(message, 'success');
            },
            error: (error) => {
                console.error('Add to cart error:', error);
                this.showMessage('Failed to add product to cart. Please try again.', 'error');
            },
            complete: () => {
                // Remove loading state
                button.classList.remove('loading');
                button.textContent = originalText;
            }
        });
    }
    
    showMessage(message, type = 'success') {
        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `ingredient-message ${type}`;
        messageEl.textContent = message;
        
        // Add to container
        this.container.appendChild(messageEl);
        
        // Remove after 3 seconds
        setTimeout(() => {
            messageEl.remove();
        }, 3000);
    }
    
    setupModalEvents() {
        const closeBtn = this.modal.querySelector('.modal-close');
        const overlay = this.modal.querySelector('.modal-overlay');
        const prevBtn = this.modal.querySelector('.modal-prev');
        const nextBtn = this.modal.querySelector('.modal-next');
        
        closeBtn?.addEventListener('click', () => this.closeModal());
        overlay?.addEventListener('click', () => this.closeModal());
        prevBtn?.addEventListener('click', () => this.modalPrev());
        nextBtn?.addEventListener('click', () => this.modalNext());
        
        // Add to Cart in modal
        const modalAddToCartBtn = this.modal.querySelector('.modal-add-to-cart');
        if (modalAddToCartBtn) {
            modalAddToCartBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleAddToCart(modalAddToCartBtn);
            });
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.modal.classList.contains('active')) return;
            
            switch (e.key) {
                case 'Escape':
                    this.closeModal();
                    break;
                case 'ArrowLeft':
                    this.modalPrev();
                    break;
                case 'ArrowRight':
                    this.modalNext();
                    break;
            }
        });
    }
    
    setupTouchEvents() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        
        this.track.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
        });
        
        this.track.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
        });
        
        this.track.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            const diff = startX - currentX;
            const threshold = 50;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        });
    }
    
    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.updateCarousel();
            this.updateButtons();
        }
    }
    
    next() {
        if (this.currentIndex < this.maxIndex) {
            this.currentIndex++;
            this.updateCarousel();
            this.updateButtons();
        }
    }
    
    updateCarousel() {
        const cardWidth = this.cards[0]?.offsetWidth || 0;
        const gap = 20;
        const translateX = -(this.currentIndex * (cardWidth + gap));
        
        this.track.style.transform = `translateX(${translateX}px)`;
    }
    
    updateButtons() {
        if (this.prevBtn) {
            this.prevBtn.disabled = this.currentIndex === 0;
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = this.currentIndex >= this.maxIndex;
        }
    }
    
    openModal(index) {
        if (!this.modal || !this.ingredientsData[index]) return;
        
        this.currentModalIndex = index;
        this.updateModalContent();
        this.modal.classList.add('active');
        this.modal.setAttribute('aria-hidden', 'false');
        
        // Focus management
        const closeBtn = this.modal.querySelector('.modal-close');
        closeBtn?.focus();
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    closeModal() {
        if (!this.modal) return;
        
        this.modal.classList.remove('active');
        this.modal.setAttribute('aria-hidden', 'true');
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Return focus to the card that opened the modal
        const card = this.cards[this.currentModalIndex];
        card?.focus();
    }
    
    modalPrev() {
        if (this.currentModalIndex > 0) {
            this.currentModalIndex--;
            this.updateModalContent();
        }
    }
    
    modalNext() {
        if (this.currentModalIndex < this.ingredientsData.length - 1) {
            this.currentModalIndex++;
            this.updateModalContent();
        }
    }
    
    updateModalContent() {
        const ingredient = this.ingredientsData[this.currentModalIndex];
        if (!ingredient) return;
        
        const image = this.modal.querySelector('#modal-ingredient-image');
        const title = this.modal.querySelector('#modal-ingredient-title');
        const description = this.modal.querySelector('#modal-ingredient-description');
        const prevBtn = this.modal.querySelector('.modal-prev');
        const nextBtn = this.modal.querySelector('.modal-next');
        
        if (image && ingredient.ingredient_image) {
            image.src = ingredient.ingredient_image.sizes?.large || ingredient.ingredient_image.url;
            image.alt = ingredient.ingredient_title;
        }
        
        if (title) {
            title.textContent = ingredient.ingredient_title.toUpperCase();
        }
        
        if (description) {
            description.textContent = ingredient.ingredient_long_description || ingredient.ingredient_description;
        }
        
        // Update navigation buttons
        if (prevBtn) {
            prevBtn.disabled = this.currentModalIndex === 0;
        }
        if (nextBtn) {
            nextBtn.disabled = this.currentModalIndex === this.ingredientsData.length - 1;
        }
    }
}

// Initialize carousels when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const carousels = document.querySelectorAll('.ingredients-carousel-container');
    carousels.forEach(carousel => {
        new IngredientCarousel(carousel);
    });
});

// Re-initialize on AJAX content load (for WooCommerce)
document.addEventListener('wc_fragments_refreshed', () => {
    const carousels = document.querySelectorAll('.ingredients-carousel-container');
    carousels.forEach(carousel => {
        if (!carousel.dataset.initialized) {
            new IngredientCarousel(carousel);
            carousel.dataset.initialized = 'true';
        }
    });
});
