/**
 * Richtlijn specific editor functions
 */

if ( wp ) {
    wp.domReady( () => {
        // Remove Richtlijn Taxonomy panel from sidebar
        // console.log(`gc-richtlijn-editor.js`);
        wp.data.dispatch( 'core/editor').removeEditorPanel( 'taxonomy-panel-richtlijn' );
        // Check:
        // wp.data.dispatch( 'core/editor').toggleEditorPanelEnabled( 'taxonomy-panel-richtlijn' );
        // wp.data.select( 'core/editor' ).getCurrentPost().template
    } );
}
