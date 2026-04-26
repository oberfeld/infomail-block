/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
// import './editor.scss';

import { PanelBody, DatePicker } from '@wordpress/components';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes }) {
	const { startDate, endDate } = attributes

	const formatDate = ( value ) => {
		if ( ! value ) {
			return '—';
		}

		const valueString = String( value );
		const isoDateMatch = valueString.match( /^(\d{4})-(\d{2})-(\d{2})/ );

		if ( isoDateMatch ) {
			const [ , year, month, day ] = isoDateMatch;
			return `${ day }.${ month }.${ year }`;
		}

		const parsedDate = new Date( valueString );

		if ( Number.isNaN( parsedDate.getTime() ) ) {
			return valueString;
		}

		const day = String( parsedDate.getDate() ).padStart( 2, '0' );
		const month = String( parsedDate.getMonth() + 1 ).padStart( 2, '0' );
		const year = parsedDate.getFullYear();

		return `${ day }.${ month }.${ year }`;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Zeitperiode', 'infomail-block')}>
					<fieldset style={{ width: '100%' }}>
                            <legend style={{ fontWeight: 600, marginBottom: 8 }}>
                                {__('Startdatum', 'infomail-block')}
                            </legend>
					<DatePicker
						currentDate ={ startDate || '' }
                        onChange={ ( value ) =>
                            setAttributes( { startDate: value } )
                        }
						/>
					</fieldset>
					<fieldset style={{ width: '100%' }}>
                            <legend style={{ fontWeight: 600, marginBottom: 8 }}>
                                {__('Enddatum', 'infomail-block')}
                            </legend>
					<DatePicker
						currentDate ={ endDate || '' }
                        onChange={ ( value ) =>
                            setAttributes( { endDate: value } )
                        }
						/>
					</fieldset>
                </PanelBody>
			</InspectorControls>
			<p { ...useBlockProps() }>
				<em><strong>Infomail Block</strong> – Beiträge vom { formatDate( startDate ) } bis und mit { formatDate( endDate ) }</em>
			</p>
		</>
	);
}
