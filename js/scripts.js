/**
 * dopagency - scripts.js
 * Full Hotwired Turbo Drive Support & Interactive Handlers
 */

(function ($) {
  "use strict";

  // Global Swiper Instances tracker
  window.dopSwipers = window.dopSwipers || {};

  function destroyAllSwipers() {
    if (window.dopSwipers) {
      Object.keys(window.dopSwipers).forEach(function (key) {
        if (window.dopSwipers[key] && typeof window.dopSwipers[key].destroy === 'function') {
          try {
            window.dopSwipers[key].destroy(true, true);
          } catch (e) {}
        }
      });
      window.dopSwipers = {};
    }
  }

  // --- MENU OVERLAY CONTROLLERS ---
  function closeSiteNavigation() {
    $("body").removeClass("overflow");
    $(".site-navigation").removeClass("active");
    $(".site-navigation").css("transition-delay", "0.5s");
    $(".site-navigation .layer").css("transition-delay", "0.3s");
    $(".site-navigation .inner").css("transition-delay", "0s");
    $(".hamburger").removeClass("is-opened-navi");
  }

  function openSiteNavigation() {
    $(".site-navigation").addClass("active");
    $("body").addClass("overflow");
    $(".site-navigation.active").css("transition-delay", "0s");
    $(".site-navigation.active .layer").css("transition-delay", "0.2s");
    $(".site-navigation.active .inner").css("transition-delay", "0.7s");
    $(".hamburger").addClass("is-opened-navi");
  }

  function closeSocialMedia() {
    $("body").removeClass("overflow");
    $(".social-media").removeClass("active");
    $(".social-media").css("transition-delay", "0.5s");
    $(".social-media .layer").css("transition-delay", "0.3s");
    $(".social-media .inner").css("transition-delay", "0s");
  }

  function openSocialMedia() {
    $(".social-media").addClass("active");
    $("body").addClass("overflow");
    $(".social-media.active").css("transition-delay", "0s");
    $(".social-media.active .layer").css("transition-delay", "0.2s");
    $(".social-media.active .inner").css("transition-delay", "0.7s");
  }

  function closeAllCases() {
    $("body").removeClass("overflow");
    $(".all-cases").removeClass("active");
    $(".all-cases").css("transition-delay", "0.5s");
    $(".all-cases .layer").css("transition-delay", "0.3s");
    $(".all-cases .inner").css("transition-delay", "0s");
  }

  function openAllCases() {
    $(".all-cases").addClass("active");
    $("body").addClass("overflow");
    $(".all-cases.active").css("transition-delay", "0s");
    $(".all-cases.active .layer").css("transition-delay", "0.2s");
    $(".all-cases.active .inner").css("transition-delay", "0.7s");
  }

  // --- FORM INPUT LABEL ---
  function checkForInput(element) {
    var $label = $(element).siblings('span');
    if ($(element).val().length > 0) {
      $label.addClass('label-up');
    } else {
      $label.removeClass('label-up');
    }
  }

  // --- SWIPER INITIALIZATION ---
  function initSwipers() {
    destroyAllSwipers();

    // Main slider & Thumbs (Home)
    if ($('.gallery-top').length && $('.gallery-thumbs').length) {
      window.dopSwipers.galleryThumbs = new Swiper('.gallery-thumbs', {
        spaceBetween: 10,
        centeredSlides: true,
        slidesPerView: 3,
        touchRatio: 0,
        slideToClickedSlide: false,
        loop: true,
        loopedSlides: 3,
        allowTouchMove: false,
        breakpoints: {
          1024: { slidesPerView: 3 },
          768: { slidesPerView: 1 },
          640: { slidesPerView: 1 },
          320: { slidesPerView: 1 }
        }
      });

      window.dopSwipers.galleryTop = new Swiper('.gallery-top', {
        spaceBetween: 0,
        autoplay: {
          delay: 9500,
          disableOnInteraction: false,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        pagination: {
          el: '.swiper-pagination',
          type: 'progressbar',
        },
        loop: true,
        loopedSlides: 3,
        allowTouchMove: false,
        thumbs: {
          swiper: window.dopSwipers.galleryThumbs
        }
      });

      window.dopSwipers.galleryTop.controller.control = window.dopSwipers.galleryThumbs;
      window.dopSwipers.galleryThumbs.controller.control = window.dopSwipers.galleryTop;
    }

    // Office slider
    if ($('.office-slider').length) {
      window.dopSwipers.officeSlider = new Swiper('.office-slider', {
        slidesPerView: '1',
        spaceBetween: 0,
        centeredSlides: true,
        loop: true,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
      });
    }

    // Carousel slider
    if ($('.carousel-slider').length) {
      window.dopSwipers.carouselSlider = new Swiper('.carousel-slider', {
        spaceBetween: 0,
        slidesPerView: 3,
        centeredSlides: true,
        autoplay: {
          delay: 9500,
          disableOnInteraction: false,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        pagination: {
          el: '.swiper-pagination',
          type: 'progressbar',
        },
        loop: true,
        breakpoints: {
          1024: { slidesPerView: 3 },
          768: { slidesPerView: 2 },
          640: { slidesPerView: 1 },
          320: { slidesPerView: 1 }
        }
      });
    }

    // Testimonials slider
    if ($('.testimonials-slider').length) {
      window.dopSwipers.testimonialsSlider = new Swiper('.testimonials-slider', {
        slidesPerView: '1',
        spaceBetween: 0,
        centeredSlides: true,
        loop: true,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
      });
    }
  }

  // --- ATTACH GLOBAL DELEGATED LISTENERS (ONLY ONCE) ---
  if (!window.__dopGlobalDelegatedListenersAttached) {
    window.__dopGlobalDelegatedListenersAttached = true;

    // HAMBURGER MENU CLICK
    $(document).on('click', '.hamburger', function (e) {
      e.preventDefault();
      if ($(".site-navigation").hasClass("active")) {
        closeSiteNavigation();
      } else {
        openSiteNavigation();
      }
    });

    // AUTO CLOSE NAVIGATION WHEN CLICKING A LINK
    $(document).on('click', '.site-navigation a', function () {
      var href = $(this).attr('href');
      if (href && href !== '#' && !href.startsWith('javascript:')) {
        closeSiteNavigation();
      }
    });

    // TREE MENU (SUBMENUS)
    $(document).on('click', '.site-navigation .inner ul li i', function (e) {
      e.preventDefault();
      $(this).parent().children('.site-navigation .inner ul li ul').slideToggle(300);
      return true;
    });

    // FOLLOW US (SOCIAL MEDIA)
    $(document).on('click', '.follow-us', function (e) {
      e.preventDefault();
      if ($(".social-media").hasClass("active")) {
        closeSocialMedia();
      } else {
        openSocialMedia();
      }
    });

    $(document).on('click', '.social-media a', function () {
      closeSocialMedia();
    });

    // ALL CASES LINK
    $(document).on('click', '.all-cases-link b', function (e) {
      e.preventDefault();
      if ($(".all-cases").hasClass("active")) {
        closeAllCases();
      } else {
        openAllCases();
      }
    });

    $(document).on('click', '.all-cases a', function () {
      closeAllCases();
    });

    // ICON CONTENT BLOCK HOVER
    $(document).on('mouseenter', '.icon-content-block .content-block', function () {
      $('.icon-content-block .content-block.selected').removeClass('selected');
      $(this).addClass('selected');
    });

    // INPUT LABELS
    $(document).on('change keyup', 'input, textarea', function () {
      checkForInput(this);
    });

    // ODOMETER / SCROLL LISTENER
    $(document).on('scroll.dopScroll', function () {
      $('.odometer').each(function () {
        var $section = $(this).closest('section');
        if ($section.length) {
          var parent_section_pos = $section.position();
          if (parent_section_pos && $(document).scrollTop() > parent_section_pos.top - 300) {
            if ($(this).data('status') == 'yes') {
              $(this).html($(this).data('count'));
              $(this).data('status', 'no');
            }
          }
        }
      });
    });

    // BFCache / History navigation reload if needed
    window.onpageshow = function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    };
  }

  // --- PAGE RE-INITIALIZATION (RUNS ON INITIAL LOAD & TURBO:LOAD) ---
  function initPage() {
    // Reset any open overlays and remove overflow
    closeSiteNavigation();
    closeSocialMedia();
    closeAllCases();

    // Mark body as page-loaded to hide preloader
    $("body").addClass("page-loaded");

    // Splitting
    if (typeof Splitting !== 'undefined') {
      Splitting();
    }

    // Data background image
    $(".swiper-slide").each(function () {
      if ($(this).attr("data-background")) {
        $(this).css("background-image", "url(" + $(this).data("background") + ")");
      }
    });

    // Input form labels
    $('input, textarea').each(function () {
      checkForInput(this);
    });

    // WOW animations
    if (typeof WOW !== 'undefined') {
      new WOW({
        animateClass: 'animated',
        offset: 50
      }).init();
    }

    // Fancybox
    if (typeof $.fancybox !== 'undefined') {
      $('[data-fancybox]').fancybox();
    }

    // Footer height calculation
    $('body').css({
      'margin-bottom': $('.footer').innerHeight() || 0
    });

    // Equalizer if present
    if ($('.equalizer').length && typeof $.fn.equalizerAnimation === 'function') {
      var barsHeight = [
        [2, 13],
        [5, 22],
        [17, 8],
        [4, 18],
        [11, 3]
      ];
      $('.equalizer').equalizerAnimation(180, barsHeight);
    }

    // Swipers
    initSwipers();
  }

  // Equalizer animation helper plugin
  function randomBetween(range) {
    var min = range[0],
      max = range[1];
    if (min < 0) {
      return min + Math.random() * (Math.abs(min) + max);
    } else {
      return min + Math.random() * max;
    }
  }

  $.fn.equalizerAnimation = function (speed, barsHeight) {
    var $equalizer = $(this);
    setInterval(function () {
      $equalizer.find('span').each(function (i) {
        $(this).css({
          height: randomBetween(barsHeight[i]) + 'px'
        });
      });
    }, speed);
    $equalizer.off('click.eq').on('click.eq', function () {
      $equalizer.toggleClass('paused');
    });
  };

  // Turbo before-cache: clean up state before snapshot
  document.addEventListener('turbo:before-cache', function () {
    closeSiteNavigation();
    closeSocialMedia();
    closeAllCases();
    destroyAllSwipers();
  });

  // Turbo load
  document.addEventListener('turbo:load', function () {
    initPage();
  });

  // Fallback for initial non-Turbo load if turbo:load didn't fire
  $(document).ready(function () {
    initPage();
  });

})(jQuery);
