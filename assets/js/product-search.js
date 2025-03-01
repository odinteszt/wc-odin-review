jQuery(document).ready(function($) {
    $("#product_name").on("keyup", function() {
        let searchQuery = $(this).val();
        if (searchQuery.length > 2) {
            $.ajax({
                url: ajaxurl, // WordPress AJAX endpoint
                type: "POST",
                data: {
                    action: "search_products",
                    term: searchQuery
                },
                success: function(response) {
                    let results = JSON.parse(response);
                    let resultsDiv = $("#product_results");
                    resultsDiv.html("");

                    if (results.length > 0) {
                        results.forEach(product => {
                            resultsDiv.append(
                                `<div class="product-item" data-id="${product.id}" style="cursor:pointer;">${product.name}</div>`
                            );
                        });
                    } else {
                        resultsDiv.html("<p>Nincs találat</p>");
                    }
                }
            });
        } else {
            $("#product_results").html("");
        }
    });

    $(document).on("click", ".product-item", function() {
        let productId = $(this).data("id");
        let productName = $(this).text();
        $("#product_name").val(productName);
        $("#termek_id").val(productId);
        $("#product_results").html("");
    });
});
