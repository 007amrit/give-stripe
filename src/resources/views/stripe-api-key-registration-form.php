<div class="give-stripe-account-type-manual">
    <div class="give-stripe-account-type-manual-modal__body">
        <form>
            <fieldset>
                <label for="stripe_account_name">
                    <?php esc_html_e('Account Name', 'give-stripe'); ?>
                </label>
                <input id="stripe_account_name" type="text" name="account_name" value=""/>
                <p class="give-field-description">
                    <?php esc_html_e(
                        'Enter your account name. It will help you to identify Stripe account.',
                        'give-stripe'
                    ); ?>
                </p>
            </fieldset>
            <fieldset>
                <label for="stripe_live_secret_key">
                    <?php esc_html_e('Live Secret Key', 'give-stripe'); ?>
                </label>
                <input id="stripe_live_secret_key" type="text" placeholder="sk_live_xxxxxxxx" name="live_secret_key"
                       value=""/>
                <p class="give-field-description">
                    <?php esc_html_e(
                        'Enter your live secret key, found in your Stripe Account Settings.',
                        'give-stripe'
                    ); ?>
                </p>
            </fieldset>
            <fieldset>
                <label for="stripe_live_publishable_key">
                    <?php esc_html_e('Live Publishable Key', 'give-stripe'); ?>
                </label>
                <input id="stripe_live_publishable_key" type="text" placeholder="pk_live_xxxxxxxx"
                       name="live_publishable_key" value=""/>
                <p class="give-field-description">
                    <?php esc_html_e(
                        'Enter your live publishable key, found in your Stripe Account Settings.',
                        'give-stripe'
                    ); ?>
                </p>
            </fieldset>
            <fieldset>
                <label for="stripe_test_secret_key">
                    <?php esc_html_e('Test Secret Key', 'give-stripe'); ?>
                </label>
                <input id="stripe_test_secret_key" type="text" placeholder="sk_test_xxxxxxxx" name="test_secret_key"
                       value=""/>
                <p class="give-field-description">
                    <?php esc_html_e(
                        'Enter your test secret key, found in your Stripe Account Settings.',
                        'give-stripe'
                    ); ?>
                </p>
            </fieldset>
            <fieldset>
                <label for="stripe_test_publishable_key">
                    <?php esc_html_e('Test Publishable Key', 'give-stripe'); ?>
                </label>
                <input id="stripe_test_publishable_key" type="text" placeholder="pk_test_xxxxxxxx"
                       name="test_publishable_key" value=""/>
                <p class="give-field-description">
                    <?php esc_html_e(
                        'Enter your test publishable key, found in your Stripe Account Settings.',
                        'give-stripe'
                    ); ?>
                </p>
            </fieldset>
        </form>
    </div>
</div>
