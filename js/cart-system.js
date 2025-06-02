class CBDBeautyCart {
  constructor() {
    this.init()
  }

  init() {
    this.setupEventListeners()
    this.updateCartDisplay()
  }

  setupEventListeners() {
    // Add to cart buttons - using event delegation
    document.addEventListener("click", (e) => {
      const addToCartBtn = e.target.closest(".add-to-cart-btn")
      if (addToCartBtn) {
        e.preventDefault()
        this.handleAddToCart(addToCartBtn)
      }

      // Quantity buttons
      const quantityBtn = e.target.closest(".quantity-btn")
      if (quantityBtn) {
        e.preventDefault()
        const cartKey = quantityBtn.dataset.cartKey
        const change = quantityBtn.classList.contains("quantity-plus") ? 1 : -1
        this.updateQuantity(cartKey, change)
      }

      // Remove item buttons
      const removeBtn = e.target.closest(".remove-item")
      if (removeBtn) {
        e.preventDefault()
        this.removeItem(removeBtn.dataset.cartKey)
      }
    })

    // Handle quantity input changes
    document.addEventListener("change", (e) => {
      if (e.target.classList.contains("quantity-input")) {
        const cartKey = e.target.dataset.cartKey
        const newQuantity = Number.parseInt(e.target.value)
        this.updateQuantityDirect(cartKey, newQuantity)
      }
    })

    // Update cart on WooCommerce events
    const jQuery = window.jQuery // Declare jQuery variable
    const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
    if (jQuery && wc_add_to_cart_params) {
      jQuery(document.body).on("added_to_cart", (event, fragments, cart_hash, button) => {
        this.showMessage("Product added to cart successfully!", "success")
        this.updateCartDisplay()
        this.updateFragments(fragments)
        if (button) {
          this.setButtonLoading(button, false)
        }
      })

      jQuery(document.body).on("wc_fragments_refreshed", () => {
        this.updateCartDisplay()
      })

      jQuery(document.body).on("removed_from_cart", (event, fragments, cart_hash) => {
        this.updateFragments(fragments)
        this.updateCartDisplay()
        this.showMessage("Item removed from cart", "success")
      })

      jQuery(document.body).on("updated_cart_totals", (event, fragments) => {
        this.updateFragments(fragments)
        this.updateCartDisplay()
        this.showMessage("Cart updated successfully", "success")
      })

      // Handle AJAX errors
      jQuery(document).ajaxError((event, jqXHR, settings, error) => {
        if (settings.url && settings.url.includes("wc-ajax")) {
          console.error("WooCommerce AJAX Error:", error)
          document
            .querySelectorAll(".add-to-cart-btn.loading, .quantity-btn.loading, .remove-item.loading")
            .forEach((button) => {
              this.setButtonLoading(button, false)
            })
          this.showMessage("An error occurred. Please try again.", "error")
        }
      })
    }
  }

  async handleAddToCart(button) {
    if (!button || button.classList.contains("loading")) return

    const productId = button.dataset.productId
    const quantity = button.dataset.quantity || 1

    if (!productId) {
      console.error("Product ID not found")
      return
    }

    this.setButtonLoading(button, true)

    try {
      const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
      if (wc_add_to_cart_params) {
        await this.addToCartWooCommerce(productId, quantity, button)
      } else {
        await this.addToCartFallback(productId, quantity)
        this.setButtonLoading(button, false)
      }
    } catch (error) {
      console.error("Add to cart error:", error)
      this.showMessage("Failed to add product to cart. Please try again.", "error")
      this.setButtonLoading(button, false)
    }
  }

  async addToCartWooCommerce(productId, quantity, button) {
    return new Promise((resolve, reject) => {
      const data = {
        product_id: productId,
        quantity: quantity,
        add_to_cart: productId,
      }

      const jQuery = window.jQuery // Declare jQuery variable
      const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
      if (jQuery && wc_add_to_cart_params) {
        jQuery.ajax({
          type: "POST",
          url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "add_to_cart"),
          data: data,
          beforeSend: () => {
            this.setButtonLoading(button, true)
          },
          success: (response) => {
            if (response.error && response.product_url) {
              window.location = response.product_url
              return
            }

            // Update cart fragments
            if (response.fragments) {
              jQuery.each(response.fragments, (key, value) => {
                jQuery(key).replaceWith(value)
              })
            }

            // Trigger WooCommerce events
            jQuery(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, button])

            resolve(response)
          },
          error: (xhr, status, error) => {
            console.error("WooCommerce AJAX error:", error)
            reject(new Error(`WooCommerce add to cart failed: ${error}`))
          },
          complete: () => {
            this.setButtonLoading(button, false)
          },
        })
      }
    })
  }

  updateQuantity(cartKey, change) {
    const input = document.querySelector(`.quantity-input[data-cart-key="${cartKey}"]`)
    if (!input) {
      // Try alternative selector for cart page
      const quantityDisplay = document.querySelector(`.quantity-display[data-cart-key="${cartKey}"]`)
      if (quantityDisplay) {
        const currentQty = Number.parseInt(quantityDisplay.textContent)
        const newQty = Math.max(1, currentQty + change)
        this.updateQuantityDirect(cartKey, newQty)
        return
      }
      return
    }

    const currentQty = Number.parseInt(input.value)
    const newQty = Math.max(1, currentQty + change)
    this.updateQuantityDirect(cartKey, newQty)
  }

  updateQuantityDirect(cartKey, newQuantity) {
    const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
    const jQuery = window.jQuery // Declare jQuery variable
    if (wc_add_to_cart_params && jQuery) {
      const $elements = jQuery(`[data-cart-key="${cartKey}"]`)
      $elements.addClass("loading")

      jQuery.ajax({
        type: "POST",
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "update_cart"),
        data: {
          cart_item_key: cartKey,
          quantity: newQuantity,
        },
        success: (response) => {
          if (response.success) {
            // Update the input value
            const input = document.querySelector(`.quantity-input[data-cart-key="${cartKey}"]`)
            if (input) {
              input.value = newQuantity
            }

            // Update fragments if available
            if (response.fragments) {
              this.updateFragments(response.fragments)
            }

            this.updateCartDisplay()
            this.showMessage("Cart updated successfully", "success")

            // Trigger WooCommerce event
            jQuery(document.body).trigger("updated_cart_totals", [response.fragments])
          } else {
            this.showMessage("Failed to update cart", "error")
          }
        },
        error: (xhr, status, error) => {
          console.error("Update quantity error:", error)
          this.showMessage("Failed to update quantity", "error")
        },
        complete: () => {
          $elements.removeClass("loading")
        },
      })
    }
  }

  removeItem(cartKey) {
    if (!confirm("Are you sure you want to remove this item from your cart?")) return

    const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
    const jQuery = window.jQuery // Declare jQuery variable
    if (wc_add_to_cart_params && jQuery) {
      const $elements = jQuery(`[data-cart-key="${cartKey}"]`)
      $elements.addClass("loading")

      jQuery.ajax({
        type: "POST",
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "remove_from_cart"),
        data: {
          cart_item_key: cartKey,
        },
        success: (response) => {
          if (response.success) {
            // Remove the item from DOM
            const $cartItem = $elements.closest(".cart-item")
            $cartItem.fadeOut(300, function () {
              jQuery(this).remove()
              // If cart is empty, reload page to show empty cart state
              if (jQuery(".cart-item").length === 0) {
                window.location.reload()
              }
            })

            // Update cart fragments
            if (response.fragments) {
              this.updateFragments(response.fragments)
            }

            this.updateCartDisplay()
            this.showMessage("Item removed from cart", "success")

            // Trigger WooCommerce event
            jQuery(document.body).trigger("removed_from_cart", [response.fragments, response.cart_hash])
          } else {
            this.showMessage(response.data?.message || "Failed to remove item", "error")
          }
        },
        error: (xhr, status, error) => {
          console.error("Remove item error:", error)
          this.showMessage("Failed to remove item. Please try again.", "error")
        },
        complete: () => {
          $elements.removeClass("loading")
        },
      })
    }
  }

  updateFragments(fragments) {
    const jQuery = window.jQuery // Declare jQuery variable
    if (fragments && jQuery) {
      jQuery.each(fragments, (key, value) => {
        const $element = jQuery(key)
        if ($element.length) {
          $element.replaceWith(value)
        }
      })

      // Update cart count from fragments if available
      if (fragments[".cart-count"]) {
        const count = jQuery(fragments[".cart-count"]).text()
        this.updateCartCount(count)
      }

      // Update mini cart count if available
      if (fragments[".mini-cart-count"]) {
        jQuery(".mini-cart-count").replaceWith(fragments[".mini-cart-count"])
      }

      // Update cart total if available
      if (fragments[".cart-total-amount"]) {
        jQuery(".cart-total-amount").replaceWith(fragments[".cart-total-amount"])
      }
    }
  }

  updateCartDisplay() {
    const wc_add_to_cart_params = window.wc_add_to_cart_params // Declare wc_add_to_cart_params variable
    const jQuery = window.jQuery // Declare jQuery variable
    if (wc_add_to_cart_params && jQuery) {
      // Get current cart count from WooCommerce
      jQuery.ajax({
        type: "GET",
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "get_refreshed_fragments"),
        success: (response) => {
          if (response.fragments) {
            this.updateFragments(response.fragments)
          }
        },
        error: (xhr, status, error) => {
          console.error("Update cart display error:", error)
        },
      })
    }
  }

  updateCartCount(count) {
    const cartCountElements = document.querySelectorAll(".cart-count, .mini-cart-count")
    cartCountElements.forEach((element) => {
      if (element.classList.contains("mini-cart-count")) {
        element.textContent = count + " items"
      } else {
        element.textContent = count || "0"
      }
    })
  }

  setButtonLoading(button, loading) {
    if (!button) return

    if (loading) {
      button.classList.add("loading")
      button.disabled = true
      button.dataset.originalText = button.querySelector(".button-text")?.textContent || button.textContent
      const buttonText = button.querySelector(".button-text")
      if (buttonText) {
        buttonText.textContent = "Adding..."
      } else {
        button.textContent = "Adding..."
      }
    } else {
      button.classList.remove("loading")
      button.disabled = false
      const buttonText = button.querySelector(".button-text")
      if (buttonText) {
        buttonText.textContent = button.dataset.originalText || "Add to Cart"
      } else {
        button.textContent = button.dataset.originalText || "Add to Cart"
      }
    }
  }

  async addToCartFallback(productId, quantity) {
    const formData = new FormData()
    formData.append("action", "cbd_beauty_add_to_cart")
    formData.append("product_id", productId)
    formData.append("quantity", quantity)
    const cbd_beauty_ajax = window.cbd_beauty_ajax // Declare cbd_beauty_ajax variable
    if (cbd_beauty_ajax) {
      formData.append("nonce", cbd_beauty_ajax.nonce)

      try {
        const response = await fetch(cbd_beauty_ajax.ajax_url, {
          method: "POST",
          body: formData,
        })

        const data = await response.json()

        if (data.success) {
          this.showMessage(data.data.message, "success")
          this.updateCartCount(data.data.cart_count)
          if (data.data.cart_total) {
            const totalElements = document.querySelectorAll(".cart-total-amount")
            totalElements.forEach((el) => (el.textContent = data.data.cart_total))
          }
        } else {
          throw new Error(data.data.message || "Unknown error")
        }
      } catch (error) {
        console.error("Fallback AJAX error:", error)
        throw error
      }
    }
  }

  showMessage(message, type = "success") {
    // Remove existing messages
    const existingMessages = document.querySelectorAll(".cart-message")
    existingMessages.forEach((msg) => msg.remove())

    // Create new message
    const messageEl = document.createElement("div")
    messageEl.className = `cart-message ${type}`
    messageEl.textContent = message

    document.body.appendChild(messageEl)

    // Show message
    setTimeout(() => {
      messageEl.classList.add("show")
    }, 100)

    // Hide message after 3 seconds
    setTimeout(() => {
      messageEl.classList.remove("show")
      setTimeout(() => {
        messageEl.remove()
      }, 300)
    }, 3000)
  }

  updateQuantityDisplay(cartKey, newQuantity) {
    // Update input if it exists
    const input = document.querySelector(`.quantity-input[data-cart-key="${cartKey}"]`)
    if (input) {
      input.value = newQuantity
    }

    // Update display if it exists
    const display = document.querySelector(`.quantity-display[data-cart-key="${cartKey}"]`)
    if (display) {
      display.textContent = newQuantity
    }
  }
}

// Initialize cart system when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  new CBDBeautyCart()
})

// Re-initialize on AJAX content load
const jQuery = window.jQuery // Declare jQuery variable
if (jQuery) {
  jQuery(document.body).on("wc_fragments_refreshed", () => {
    new CBDBeautyCart()
  })
}
