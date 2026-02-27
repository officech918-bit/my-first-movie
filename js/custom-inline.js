// js/custom-inline.js

document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Enrollment button clicks
    document.querySelectorAll('.edit-enrollment').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            window.open(`current-enrollments.php?id=${id}`, 'asdas', 'toolbars=0,width=1080,height=720,left=200,top=200,scrollbars=1,resizable=1');
        });
    });

    // Handle Delete Enrollment button clicks
    document.querySelectorAll('.delete-enrollment').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm("Are you sure you want to delete this enrollment? This action cannot be undone.")) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    });
});


// This file consolidates inline scripts from across the site to comply with Content Security Policy.

// 1. Accordion and Carousel Initialization for behind-the-scenes.php
jQuery(document).ready(function($) {
    // This logic should only run on the behind-the-scenes page.
    // We can check for a unique element on that page to be safe.
    if ($('.accordion.accordion-bg').length) {

        function initCarouselInAccordion(accContent) {
            var carousel = accContent.find('.portfolio-carousel');
            console.log('Accordion open. Searching for carousel...', carousel);
            if (carousel.length) {
                if (carousel.hasClass('owl-loaded')) {
                    console.log('Carousel was already loaded, destroying it first.');
                    carousel.trigger('destroy.owl.carousel');
                }
                console.log('Initializing carousel...');
                carousel.owlCarousel({
                    margin: 20,
                    nav: true,
                    navText: ['<i class="icon-angle-left"></i>', '<i class="icon-angle-right"></i>'],
                    autoplay: false,
                    autoplayHoverPause: true,
                    dots: false,
                    responsive: {
                        0: { items: 1 },
                        480: { items: 2 },
                        768: { items: 3 },
                        992: { items: 4 }
                    }
                });
            } else {
                console.log('Carousel element not found in this accordion content.');
            }
        }

        $('.accordion .acc_content').filter(':visible').each(function() {
            initCarouselInAccordion($(this));
        });

        $('.accordion .acctitle').on('click', function() {
            var accContent = $(this).next('.acc_content');
            setTimeout(function() {
                if (accContent.is(':visible')) {
                    initCarouselInAccordion(accContent);
                }
            }, 400);
        });
    }
});

// 2. Pulsate effect initialization
$(function() {
    if ($("#pulse").length) $("#pulse").pulsate({ color: "#09f" });
    if ($(".pulse1").length) $(".pulse1").pulsate({ glow: false });
    if ($(".pulse2").length) $(".pulse2").pulsate({ color: "#09f" });
    if ($(".pulse3").length) $(".pulse3").pulsate({ reach: 100 });
    if ($(".pulse4").length) $(".pulse4").pulsate({ speed: 2500 });
    if ($(".pulse5").length) $(".pulse5").pulsate({ pause: 1000 });
    if ($(".pulse6").length) $(".pulse6").pulsate({ onHover: true });
});

// 3. Facebook SDK Asynchronous Loader
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// New scripts from current-enrollments.php
$(document).ready(function() {
    // Initialize sheepIt plugin
    $('#sheepItForm').sheepIt({
        separator: '',
        allowRemoveLast: true,
        allowRemoveFirst: true,
        allowAdd: true,
        allowRemove: true,
        // Limits
        maxForms: 10,
        minForms: 0,
        iniForms: 0,
        afterAdd: function(source, newForm) {
            // Re-initialize any plugins for the new form if necessary
        }
    });

    // Validation Engine
    $("#enrollment").validationEngine();

    // Labelauty
    $(".to-labelauty").labelauty({
        checked_label: "",
        unchecked_label: ""
    });

    // Metronic theme initialization
    Metronic.init(); // init metronic core componets
    Layout.init(); // init layout
    Demo.init(); // init demo features
    Index.init(); // init index page
    Tasks.initDashboardWidget(); // init dashboard widget

    // Handle category change to update fee
    $('.ecategory').on('change', function() {
        var selectedCategory = $(this).val();
        var fee = window.categoryFees[selectedCategory] || 0;
        $('#damount').text(fee.toFixed(2));
        $('#hfee').val(fee);
    });

    // Initialize fee display on page load
    var initialCategory = $('.ecategory:checked').val();
    if (initialCategory) {
        var fee = window.categoryFees[initialCategory] || 0;
        $('#damount').text(fee.toFixed(2));
        $('#hfee').val(fee);
    }

    // Cancel button handler
    $('#btnc').click(function() {
        window.location.href = "dashboard.php";
    });

    // CKEditor initialization
    CKEDITOR.replace('editor2', {
        filebrowserBrowseUrl: 'ckfinder/ckfinder.html',
        filebrowserUploadUrl: 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserWindowWidth: '1000',
        filebrowserWindowHeight: '700'
    });
});