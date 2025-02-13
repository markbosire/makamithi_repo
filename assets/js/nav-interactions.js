
  // Mobile menu toggle
  $('#mobile-menu-toggle').on('click', function() {
    $('#mobile-menu').removeClass('translate-x-full');
  });

  // Close mobile menu
  $('#mobile-menu-close').on('click', function() {
    $('#mobile-menu').addClass('translate-x-full');
  });

  // Mobile dropdown interactions
  $('.mobile-dropdown > button').on('click', function() {
    const dropdownContent = $(this).next('.mobile-dropdown-content');
    const isHidden = dropdownContent.hasClass('hidden');
    
    // Close all other dropdowns first
    $('.mobile-dropdown-content').addClass('hidden');
    
    // Toggle current dropdown
    dropdownContent.toggleClass('hidden', !isHidden);
    
    // Rotate dropdown icon
    const svg = $(this).find('svg');
    svg.toggleClass('rotate-180', !isHidden);
  });
  $(document).ready(function() {
    // Search autocomplete with jQuery
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length < 2) {
            $('#searchResults').addClass('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: `${BASE_URL}/api/search_sales.php`,
                data: { query: query },
                success: function(data) {
                    const results = JSON.parse(data);
                    const $searchResults = $('#searchResults');
                    $searchResults.empty();

                    if (results.length > 0) {
                        results.forEach(item => {
                            $searchResults.append(
                                $('<div>')
                                    .addClass('px-4 py-2 hover:bg-gray-100 cursor-pointer')
                                    .text(`${item.product_name} - ${item.client_name}`)
                                    .click(function() {
                                        $('#search').val(item.product_name);
                                        $searchResults.addClass('hidden');
                                        // Trigger search
                                        $('#searchBtn').click();
                                    })
                            );
                        });
                        $searchResults.removeClass('hidden');
                    } else {
                        $searchResults.addClass('hidden');
                    }
                }
            });
        }, 300);
    });

    // Search button click handler
    $('#searchBtn').click(function() {
        const url = new URL(window.location);
        url.searchParams.set('search', $('#search').val());
        window.location = url;
    });

    // Detail modal handler
    $('.view-details').click(function() {
        const saleId = $(this).data('id');
        $.ajax({
            url: `${BASE_URL}/api/get_sale_details.php`,
            data: { id: saleId },
            success: function(data) {
                const sale = JSON.parse(data);
                $('#modalContent').html(`
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold">Client Information</h4>
                            <p>Name: ${sale.client_name}</p>
                            <p>Email: ${sale.client_email}</p>
                            <p>Contact: ${sale.client_contact}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Product Information</h4>
                            <p>Name: ${sale.product_name}</p>
                            <p>Category: ${sale.category}</p>
                            <p>Description: ${sale.product_description}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Sale Information</h4>
                            <p>Date: ${new Date(sale.sale_date).toLocaleDateString()}</p>
                            <p>Quantity: ${sale.quantity}</p>
                            <p>Unit Price: $${sale.unit_price}</p>
                            <p>Total: $${sale.total}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Payment Information</h4>
                            <p>Method: ${sale.payment_method}</p>
                            <p>Status: ${sale.payment_status}</p>
                            <p>Notes: ${sale.notes || 'N/A'}</p>
                        </div>
                    </div>
                `);
                $('#detailModal').removeClass('hidden');
            }
        });
    });

    // Close modal
    $('.close-modal').click(function() {
        $('#detailModal').addClass('hidden');
    });

    // Print receipt handler
    $('.print-receipt').click(function() {
        const saleId = $(this).data('id');
        window.open(`${BASE_URL}/api/generate_receipt.php?id=${saleId}`, '_blank');
    });
});

