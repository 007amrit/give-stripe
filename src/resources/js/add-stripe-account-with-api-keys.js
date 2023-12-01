/* globals Give, giveStripe, ajaxurl */

const { __, sprintf } = wp.i18n;

window.addEventListener( 'DOMContentLoaded', function() {
	const addNewStripeAccountWithApiKeysBtn = document.querySelector( '.js-add-new-stripe-account' );

	if ( ! addNewStripeAccountWithApiKeysBtn ) {
		return;
	}

	addNewStripeAccountWithApiKeysBtn.addEventListener('click', (e) => {
		e.preventDefault();

		new Give.modal.GiveFormModal( {
			classes: {
				modalWrapper: 'give--stripe-add-api-key',
			},

			modalContent: {
				title: '<span class="give-stripe-icon stripe-logo-with-circle"></span>' + __( 'Add New Stripe Account', 'give-stripe' ),
				desc: giveStripe.registerStripeAccountApiKeyFormHtml,
				link: 'https://stripe.com/docs/keys',
				link_text: __( 'View Stripe API keys documentation', 'give-stripe' ),
				confirmBtnTitle: __( 'Add New Account', 'give-stripe' ),
			},

			successConfirm: async () => {
				const modelContent = jQuery.magnificPopup.instance.content[0].querySelector('.give-modal__body');
				const accountName = modelContent.querySelector( 'input[name="account_name"]' );
				const testSecretKey = modelContent.querySelector( 'input[name="test_secret_key"]' );
				const liveSecretKey = modelContent.querySelector( 'input[name="live_secret_key"]' );
				const testPublishableKey = modelContent.querySelector( 'input[name="test_publishable_key"]' );
				const livePublishableKey = modelContent.querySelector( 'input[name="live_publishable_key"]' );
				const previousError = modelContent.querySelector('.give-notice');
				const button = modelContent.nextElementSibling.querySelector('button.give-popup-form-button');
				let isDataValid = true;
				let buttonText = giveStripe.i18n.adding;

				// Remove previous error from model.
				if( previousError ) {
					previousError.remove();
				}

				button.textContent = buttonText;

				if (
					( accountName.value.trim().length === 0 ) ||
					( testSecretKey.value.trim().length === 0 ) ||
					( liveSecretKey.value.trim().length === 0 ) ||
					( testPublishableKey.value.trim().length === 0 ) ||
					( livePublishableKey.value.trim().length === 0 )
				) {
					modelContent.insertAdjacentHTML( 'beforeend', sprintf(
						`<div class="give-notice notice error notice-error"><p>%s</p></div>`,
						__( 'Please enter the test as well as live secret and publishable keys to add a Stripe account.', 'give-stripe' )
					));

					buttonText = giveStripe.i18n.add;
					isDataValid = false;
				}

				// Validate account name.
				document.querySelectorAll('.give-stripe-account-manager-list .give-stripe-account-name .give-stripe-connect-data-field').forEach(( accountNameContainer ) => {
					if( accountNameContainer.textContent.trim().toLowerCase() === accountName.value.trim().toLowerCase() ) {
						modelContent.insertAdjacentHTML( 'beforeend', sprintf(
							`<div class="give-notice notice error notice-error"><p>%s</p></div>`,
							__( 'Please choose different account name. A Stripe account already exist with this name.', 'give-stripe' )
						));

						buttonText = giveStripe.i18n.add;
						isDataValid = false;
					}
				});

				if (!isDataValid) {
					Object.assign(button, {
						textContent: buttonText,
						disabled: isDataValid
					});

					return;
				}

				const formData = new FormData();
				const postIdHiddienField = document.getElementById('post_ID');

				formData.append( 'action', 'give_stripe_add_manual_account' );
				formData.append( 'account_name', accountName.value.trim() );
				formData.append( 'live_secret_key', liveSecretKey.value.trim() );
				formData.append( 'test_secret_key', testSecretKey.value.trim() );
				formData.append( 'test_publishable_key', testPublishableKey.value.trim() );
				formData.append( 'live_publishable_key', livePublishableKey.value.trim() );

				if( postIdHiddienField ) {
					formData.append( 'form_id', postIdHiddienField.value );
				}

				const addStripeAccountResponse = await fetch(
					ajaxurl,
					{
						method: 'post',
						body: formData,
					}
				);
				const response = await addStripeAccountResponse.json();
				isDataValid = false;

				if ( ! response.success ) {
					Object.assign(button, {
						textContent: giveStripe.i18n.add,
						disabled: isDataValid
					});

					if( response.hasOwnProperty('data') && response.data.hasOwnProperty('error') ) {
						modelContent.insertAdjacentHTML( 'beforeend', sprintf(
							`<div class="give-notice notice error notice-error"><p>%s</p></div>`,
							response.data.error
						));
					}

					return;
				}

				Object.assign(button, {
					textContent: giveStripe.i18n.added,
					disabled: isDataValid
				});

				const searchParams = new URLSearchParams( window.location.search );
				searchParams.set( 'stripe_account', 'connected' );
				window.location.search = searchParams.toString();
			},
		} ).render();
	} );
});
