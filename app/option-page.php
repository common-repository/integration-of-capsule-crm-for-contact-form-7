<?php
/**
 * Option page of the plugin
 *
 * @package CF7_STREAK_CRM_INTEGRATION
 */
$validation = $this->validate_apitoken();
?>



<div class="cfcc-block">
<div class="top">
	<div class="logo">
		<h1>Capsule CRM</h1>
		<small>V<?php echo CFCC_CAPSULE_CRM_VERSION; ?></small>
	</div>
	<div class="buttons">
		<a target="_blank" href="https://www.youtube.com/watch?v=DU_IYlpig_4">Video Tutorials</a>
		<a target="_blank" href="https://wisersteps.com/plugin/contact-form-7-capsule-crm-integration/">Documentation</a>
	</div>
</div>
<div class="card">
<div class="">
  <h2 class="nav-tab-wrapper">
    <a href="admin.php?page=cfcc-capsule-crm-integration" class="nav-tab fs-tab nav-tab-active home">Settings</a>
  </h2>
	<h4>You can get these information from <a target="_blank" href="https://wisersteps.com/plugin/contact-form-7-capsule-crm-integration/">From here</a></h4>
	<form method="post" action="options.php">
		<?php
			settings_fields( 'cfcc_capsule_crm' );
			do_settings_sections( 'cfcc_capsule_crm' );
		?>
		<style>

		.cfcc-block .top {
			padding: 25px 30px;
			background: #1F2E5C;
			margin-top: 20px;
			overflow: hidden;
		}
		.cfcc-block .top .logo {
			float: left;
		}
		.cfcc-block .top h1 {
			color: #fff;
			display: inline-block;
			margin: 0;
		}
		.cfcc-block .top small {
			color: #fff;
			font-size: 12px;
		}
		.cfcc-block .top a {
			padding: 7px 25px;
			margin-right: 13px;
			border: 1px solid #fff;
			font-size: 15px;
			color: #fff;
			text-decoration: none;
		}
		.cfcc-block .buttons {
			float: right;
		}

		.cfcc-block .card {
			max-width: 100%;
			border: 0px;
			margin-top: 0px;
		}
		.cfcc-block .card h1 {
			margin: 0;
		}
		.cfcc-block .button-primary {
			background: #1F2E5C;
			border-color: #1F2E5C;
			color: #fff;
			text-decoration: none;
			text-shadow: none;
		}
		.cfcc-block .button-primary:hover {
			background: #1F2E5C;
			border-color: #1F2E5C;
			color: #fff;
			text-decoration: none;
			text-shadow: none;
		}


		.cfcc-block table span{
			background: #11bf47;
			display: inline-block;
			padding: 5px 10px;
			color: #fff;
			margin-top:10px;
		}

		.cfcc-block table span.error{
			background: #d43636;
		}

		</style>
		<table class="form-table ">
			<tbody>

				<tr>
				<th scope="row"><label for="cfcc_capsule_api_token"><?php esc_html_e( 'API Token','contact-form-7-capsule-crm' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" name="cfcc_capsule_api_token" value="<?php echo esc_attr( get_option( 'cfcc_capsule_api_token' ) ); ?>">
						<?php if(!$validation){
							echo '<span class="error">Not Connected</span>';
						}else{
							echo '<span>Connected</span>';
						} ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button(); ?>
	</form>
</div>

</div>
</div>

