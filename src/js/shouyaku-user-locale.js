/**
 * Description
 */

const { __ } = wp.i18n;

/*global ShouyakuUserLocale: false*/

(($) => {

  'use strict';

  /**
   * Get cookie as hash.
   *
   * @returns {Object}
   */
  const getCookieHash = () => {
    const cookies = {};
    if ( document.cookie ) {
      document.cookie.split('; ').forEach(( cookie ) => {
        let [ key, val ] = cookie.split( '=' ).map( $.trim );
        cookies[key] = decodeURIComponent( val );
      });
    }
    return cookies;
  };

  /**
   * Get locale from cookie.
   *
   * @returns {String}
   */
  const getCookieLocale = () => {
    const cookies = getCookieHash();
    return cookies.locale || '';
  };

  /**
   * Save user locale as cookie.
   *
   * @param {String} locale If empty, delete cookie.
   */
  const setCookieLocale = ( locale ) => {
    let cookie;
    if ( locale ) {
      // Set cookie.
      const maxAge = parseInt( ShouyakuUserLocale.maxAge ? ShouyakuUserLocale.MaxAge() : 60 * 60 * 24 * 365, 10 );
      locale = encodeURIComponent( locale );
      cookie = `locale=${locale}; max-age=${maxAge}; path=/; secure`
    } else {
      // Delete cookie.
      cookie = 'locale=; path=/; expires=' + encodeURIComponent( 'Thu, 01 Jan 1970 00:00:00 GMT' );
    }
    document.cookie = cookie;
  };

  /**
   * Get user's browser locale from User Agent.
   *
   * @return {String}
   */
  const getBrowserLocale = () => {
    if ( navigator.languages && navigator.languages.length ) {
      return navigator.languages[0];
    } else if ( navigator.language ) {
      return navigator.language;
    } else {
      return navigator.systemLanguage || navigator.browserLanguage || ShouyakuUserLocale.default;
    }
  };

  /**
   * Convert locale to lang code.
   *
   * @param {String} locale
   * @returns {String}
   */
  const localeToLang = ( locale ) => {
    let [ lang ] = locale.split( '-' );
    return lang;
  };

  $( document ).ready( () => {
    // Set locale.
    const $html           = $( 'html' );
    let cookieLocale      = getCookieLocale();
    let docLocale         = $html.attr( 'lang' );
    let profileLocale     = $html.attr( 'data-profile-locale' ).replace( '_', '-' );
    let userLocale        = profileLocale || cookieLocale || getBrowserLocale();
    const shouldTranslate = localeToLang( userLocale ) !== localeToLang( docLocale );
    $html
      .attr( 'data-user-locale', userLocale );
    if ( shouldTranslate ) {
      // let user to consider other languages.
      $html.addClass( 'recommend-translation' );
    }
    // Language switcher.
    // $( `.shouyaku-language-recommend-item[data-locale="${userLocale}"]` ).addClass( 'active' );
    $( '.shouyaku-language-recommend-item' ).click( function( e ) {
      e.preventDefault();
      let locale = $( this ).attr( 'data-locale' ).replace( '_', '-' );
      if ( profileLocale ) {
        $.post( ShouyakuUserLocale.endpoint + '/user', {
          locale: locale,
          _wpnonce: ShouyakuUserLocale.nonce
        } ).done( ( response ) => {
          window.location.reload();
        }).fail( (res) => {
          let message = __( 'Failed to change locale.', 'shouyaku' );
          if ( res.responseJSON && res.responseJSON.message ) {
            message = res.responseJSON.message;
          }
          alert( message );
        });
      } else {
        // User is not logged in.
        setCookieLocale( locale );
        window.location.reload();
      }
    });
  });

})(jQuery);
