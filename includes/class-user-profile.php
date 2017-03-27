<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 *
 * Adds function to remove person_id from a user
 *
 * @class WC_AfterPay_User_Profile
 * @version 1.0.0
 * @package WC_AfterPay/Classes
 * @category Class
 * @author Krokedil
 */

// Radera sen
// wp_get_current_user(); - få användar id för användaren som redigeras.
// get_user_meta( $user->ID, '_afterpay_personal_no'); - Få personal_no för användaren

class WC_AfterPay_User_Profile {
	public function __construct() {
		// View User profile fields
		add_action( 'show_user_profile', array( $this, 'wc_aferpay_user_id' ));
		add_action( 'edit_user_profile', array( $this, 'wc_aferpay_user_id' ));

		// Save user profile fields
		add_action( 'edit_user_profile_update', array( $this, 'save_wc_afterpay_person_id' ));
		add_action( 'personal_options_update', array( $this, 'save_wc_afterpay_person_id' ));
	}

	/**
	 * Add Add person ID to WP user profile
	 **/
	function wc_aferpay_user_id( $user ) {
		$personal_number = get_user_meta( $user->ID, '_afterpay_personal_no');
		?>
		<h3>AfterPay</h3>

		<table class="form-table">
			<tr>
				<th><label for="afterpay_profile"><?php _e( 'AfterPay Personal ID', 'woocommerce-gateway-afterpay' ); ?></label></th>
				<td><input type="text" name="afterpay_profile" value="<?php echo $personal_number[0]; ?>" class="regular-text" /></td>
			</tr>
		</table>
		<?php
	} //End function

	/**
	 * Remove the person ID from user
	 */
	function save_wc_afterpay_person_id( $id ) {

		if ( !current_user_can( 'edit_user', $id ) )
			return false;

		if ( isset( $_POST['afterpay_profile'] ) )
			update_user_meta( $id, '_afterpay_personal_no', $_POST['afterpay_profile'] );

	} //End function
}
$wc_afterpay_user_profile = new WC_AfterPay_User_Profile();