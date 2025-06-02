document.addEventListener("DOMContentLoaded", () => {
  // Handle Add to Cart buttons
  const addToCartButtons = document.querySelectorAll(".shop-add-to-cart")

  addToCartButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()

      if (this.classList.contains("out-of-stock") || this.classList.contains("loading")) {
        return
      }

      const productId = this.dataset.productId
      const productName = this.dataset.productName

      if (!productId) {
        console.error("Product ID not found")
        return
      }

      // Add loading state
      this.classList.add("loading")
      this.disabled = true

      // Check if WooCommerce AJAX is available
      const wc_add_to_cart_params = window.wc_add_to_cart_params
      const jQuery = window.jQuery
      if (typeof wc_add_to_cart_params !== "undefined" && jQuery) {
        // Use WooCommerce's built-in AJAX add to cart
        jQuery.ajax({
          type: "POST",
          url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "add_to_cart"),
          data: {
            product_id: productId,
            quantity: 1,
          },
          success: (response) => {
            if (response.error && response.product_url) {
              window.location = response.product_url
              return
            }

            if (response.fragments) {
              // Update cart fragments
              jQuery.each(response.fragments, (key, value) => {
                jQuery(key).replaceWith(value)
              })
            }

            // Trigger WooCommerce events
            jQuery(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, this])

            // Show success message
            showMessage(`${productName} added to cart successfully!`, "success")

            // Update button text temporarily
            const buttonText = this.querySelector(".button-text")
            const originalText = buttonText.textContent
            buttonText.textContent = "Added!"

            setTimeout(() => {
              buttonText.textContent = originalText
            }, 2000)
          },
          error: (xhr, status, error) => {
            console.error("Add to cart error:", error)
            showMessage("Failed to add product to cart. Please try again.", "error")
          },
          complete: () => {
            // Remove loading state
            this.classList.remove("loading")
            this.disabled = false
          },
        })
      } else {
        // Fallback AJAX method
        const cbd_beauty_ajax = window.cbd_beauty_ajax
        fetch(cbd_beauty_ajax.ajax_url, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "cbd_beauty_add_to_cart",
            nonce: cbd_beauty_ajax.nonce,
            product_id: productId,
            quantity: 1,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Update cart count and total
              if (data.data.cart_count !== undefined) {
                document.querySelectorAll(".cart-count").forEach((el) => {
                  el.textContent = data.data.cart_count
                })
              }
              if (data.data.cart_total !== undefined) {
                document.querySelectorAll(".cart-total-amount").forEach((el) => {
                  el.textContent = data.data.cart_total
                })
              }

              showMessage(`${productName} added to cart successfully!`, "success")

              // Update button text temporarily
              const buttonText = this.querySelector(".button-text")
              const originalText = buttonText.textContent
              buttonText.textContent = "Added!"

              setTimeout(() => {
                buttonText.textContent = originalText
              }, 2000)
            } else {
              showMessage(data.data.message || "Failed to add product to cart", "error")
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            showMessage("An error occurred while adding to cart", "error")
          })
          .finally(() => {
            // Remove loading state
            this.classList.remove("loading")
            this.disabled = false
          })
      }
    })
  })

  // Show message function
  function showMessage(message, type = "success") {
    // Remove existing messages
    const existingMessages = document.querySelectorAll(".shop-message")
    existingMessages.forEach((msg) => msg.remove())

    // Create message element
    const messageEl = document.createElement("div")
    messageEl.className = `shop-message ${type}`
    messageEl.textContent = message

    // Add to body
    document.body.appendChild(messageEl)

    // Show message with animation
    setTimeout(() => {
      messageEl.classList.add("show")
    }, 100)

    // Remove after 3 seconds
    setTimeout(() => {
      messageEl.classList.remove("show")
      setTimeout(() => {
        messageEl.remove()
      }, 300)
    }, 3000)
  }

  // Handle quantity selectors if they exist
  const quantityInputs = document.querySelectorAll('.quantity input[type="number"]')
  quantityInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const min = Number.parseInt(this.getAttribute("min")) || 1
      const max = Number.parseInt(this.getAttribute("max")) || 999
      const value = Number.parseInt(this.value)

      if (isNaN(value) || value < min) {
        this.value = min
      } else if (value > max) {
        this.value = max
      }
    })
  })

  // Handle product filtering and sorting
  const orderingSelect = document.querySelector(".orderby")
  if (orderingSelect) {
    orderingSelect.addEventListener("change", function () {
      this.closest("form").submit()
    })
  }
})

// Handle WooCommerce events
if (window.jQuery) {
  window.jQuery(document).ready(($) => {
    // Update cart count when items are added
    $(window.document.body).on("added_to_cart", (event, fragments, cart_hash, button) => {
      // Update cart fragments
      if (fragments) {
        $.each(fragments, (key, value) => {
          $(key).replaceWith(value)
        })
      }

      // Add visual feedback to the button
      if (button) {
        $(button).addClass("added-to-cart")
        setTimeout(() => {
          $(button).removeClass("added-to-cart")
        }, 2000)
      }
    })

    // Handle cart fragments refresh
    $(window.document.body).on("wc_fragments_refreshed", () => {
      console.log("Cart fragments refreshed")
    })
  })
}
