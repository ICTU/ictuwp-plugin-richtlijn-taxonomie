/**
 * Richtlijn specific editor functions
 */

if ( wp ) {
    wp.domReady( () => {
        // Remove Richtlijn Taxonomy panel from sidebar
        // on pages that have the Richtlijn Detail Page template
        wp.data.dispatch( 'core/editor').removeEditorPanel( 'taxonomy-panel-richtlijn' );
    } );
}
