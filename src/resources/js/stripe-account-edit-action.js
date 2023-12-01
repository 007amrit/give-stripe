/* globals Give, giveStripe, ajaxurl */

const { __, sprintf } = wp.i18n;

window.addEventListener( 'DOMContentLoaded', function() {
	const editManualStripeAccountBtn = document.querySelector( '.give-stripe-account-edit .js-edit-manual-account' );

	if ( ! editManualStripeAccountBtn ) {
		return;
	}

	editManualStripeAccountBtn.addEventListener('click', (e) => {
		e.preventDefault();

		const getStripeAccountDetailsFromData = new FormData();
		const clickedButton = e.target;
		const stripeAccountSlug = clickedButton.parentElement.parentElement
			.querySelector( '.give-stripe-account-manager-list-item input[name="stripe-account-slug"]' )
			.value.trim();

		getStripeAccountDetailsFromData.append(
			'action',
			'give_stripe_account_get_details'
		);
		getStripeAccountDetailsFromData.append(
			'account_slug',
			stripeAccountSlug
		);

		fetch(
			ajaxurl,
			{
				method: 'post',
				body: getStripeAccountDetailsFromData,
			}
			).then( response => response.json() )
			.then( response => {
				// Exit
				if( ! response.success ) {
					return;
				}

				new Give.modal.GiveFormModal( {
					classes: {
						modalWrapper: 'give--stripe-add-api-key',
					},

					modalContent: {
						title: '<span class="give-stripe-icon stripe-logo-with-circle"></span>' + __( 'Edit Stripe Account', 'give-stripe' ),
						desc: giveStripe.registerStripeAccountApiKeyFormHtml,
						link: 'https://stripe.com/docs/keys',
						link_text: __( 'View Stripe API keys documentation', 'give-stripe' ),
						confirmBtnTitle: __( 'Edit Account', 'give-stripe' ),
					},


					callbacks: {
						open: function () {
							const modelContent = jQuery.magnificPopup.instance.content[0].querySelector('.give-modal__body');
							const accountName = modelContent.querySelector( 'input[name="account_name"]' );
							const testSecretKey = modelContent.querySelector( 'input[name="test_secret_key"]' );
							const liveSecretKey = modelContent.querySelector( 'input[name="live_secret_key"]' );
							const testPublishableKey = modelContent.querySelector( 'input[name="test_publishable_key"]' );
							const livePublishableKey = modelContent.querySelector( 'input[name="live_publishable_key"]' );

							accountName.setAttribute( 'value', response.data.account_name );
							testSecretKey.setAttribute( 'value', response.data.test_secret_key );
							liveSecretKey.setAttribute( 'value', response.data.live_secret_key );
							testPublishableKey.setAttribute( 'value', response.data.test_publishable_key );
							livePublishableKey.setAttribute( 'value', response.data.live_publishable_key );
						},
					},

					successConfirm() {
						const modelContent = jQuery.magnificPopup.instance.content[0].querySelector('.give-modal__body');
						const accountName = modelContent.querySelector( 'input[name="account_name"]' );
						const testSecretKey = modelContent.querySelector( 'input[name="test_secret_key"]' );
						const liveSecretKey = modelContent.querySelector( 'input[name="live_secret_key"]' );
						const testPublishableKey = modelContent.querySelector( 'input[name="test_publishable_key"]' );
						const livePublishableKey = modelContent.querySelector( 'input[name="live_publishable_key"]' );
						const previousError = modelContent.querySelector('.give-notice');
						const button = modelContent.nextElementSibling.querySelector('button.give-popup-form-button');
						const isAnyApiKeyEdited = testSecretKey.value.trim() !== response.data.test_secret_key ||
								liveSecretKey.value.trim() !== response.data.live_secret_key ||
								testPublishableKey.value.trim() !== response.data.test_publishable_key ||
								livePublishableKey.value.trim() !== response.data.live_publishable_key;

						// Remove previous error from model.
						if( previousError ) {
							previousError.remove();
						}

						button.textContent = giveStripe.i18n.adding;
						button.disabled = true;

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

							button.textContent = giveStripe.i18n.add;
							button.disabled = false;
						}

						// Validate account name.
						if( accountName.value.trim().toLowerCase() !== response.data.account_name.toLowerCase() ){
							document.querySelectorAll('.give-stripe-account-manager-list .give-stripe-account-name .give-stripe-connect-data-field').forEach(( accountNameContainer ) => {
								if( accountNameContainer.textContent.trim().toLowerCase() === accountName.value.trim().toLowerCase() ) {
									modelContent.insertAdjacentHTML( 'beforeend', sprintf(
										`<div class="give-notice notice error notice-error"><p>%s</p></div>`,
										__( 'Please choose different account name. A Stripe account already exist with this name.', 'give-stripe' )
									));

									button.textContent = giveStripe.i18n.add;
									button.disabled = false;
								}
							});
						}

						if( ! button.disabled ) {
							return;
						}

						const formData = new FormData();

						formData.append( 'action', 'give_stripe_update_manual_account' );
						formData.append( 'account_slug', stripeAccountSlug );
						formData.append( 'account_name', accountName.value.trim() );
						formData.append( 'live_secret_key', liveSecretKey.value.trim() );
						formData.append( 'test_secret_key', testSecretKey.value.trim() );
						formData.append( 'test_publishable_key', testPublishableKey.value.trim() );
						formData.append( 'live_publishable_key', livePublishableKey.value.trim() );

						fetch(
							ajaxurl,
							{
								method: 'post',
								body: formData,
							}
						)
							.then( ( response ) => response.json() )
							.then( ( response ) => {
								button.disabled = false;

								if ( ! response.success ) {
									button.textContent = giveStripe.i18n.add;

									if( response.hasOwnProperty('data') && response.data.hasOwnProperty('error') ) {
										modelContent.insertAdjacentHTML( 'beforeend', sprintf(
											`<div class="give-notice notice error notice-error"><p>%s</p></div>`,
											response.data.error
										));
									}

									return;
								}

								button.textContent = giveStripe.i18n.added;

								if( isAnyApiKeyEdited ) {
									const searchParams = new URLSearchParams( window.location.search );
									searchParams.set( 'stripe_account', 'connected' );
									window.location.search = searchParams.toString();
								} else {
									window.location.reload();
								}

							} );
					},
				} ).render();
			});
	} );
});
