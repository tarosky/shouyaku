/**
 * Term editor.
 */

jQuery( document ).ready( ( $ ) => {

  // Language switcher.
  $( '#shouyaku-lang-selector' ).change( function( e ) {
    let current = $( this ).val();
    $( '.shouyaku-term-editor' ).each( function( index, div ) {
      let locale = $( div ).attr( 'data-locale' );
      $( div ).attr( 'hidden', current === locale ? null : true );
    });
  });
});
