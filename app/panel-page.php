<?php

/**
 * Option page of the plugin
 *
 * @package CF7_capsule_crom_INTEGRATION
 */
namespace CFCC_CAPSULE_CRM;

$id = ( empty($_POST['post_ID']) ? absint( $_REQUEST['post'] ) : absint( $_POST['post_ID'] ) );
$cf_post = \WPCF7_ContactForm::get_instance( $id );
$form_tags = $cf_post->collect_mail_tags();
$logging = esc_attr( get_post_meta( $id, 'cfcc_logging', true ) );
?>



<!-- RTL Style -->
<?php 
if ( is_rtl() ) {
    echo  '
		<style>
			.capsule-crm-float{
				right:auto;
				left:0;
			}
		</style>
	' ;
}
?>
<div class="capsule_crm_page">
	
	<table class="form-table">
		<tbody>
			<!-- Reset cache -->
			<tr>
				<th scope="row">
					<label for="cfcc_reset_cache"><?php 
echo  __( 'Reset Cache', 'contact-form-7-capsule-crm' ) ;
?></label>
				</th>
				<td>
					<a class="button" href="?cfcc_reset_cache"><?php 
echo  __( 'Reset Cache', 'contact-form-7-capsule-crm' ) ;
?></a>
					<p><?php 
echo  __( 'We are caching main endpoints like (Owners,Custom fields, Milestones, Parties, Tags) so we can limit the number of requests in the dashboard', 'contact-form-7-capsule-crm' ) ;
?></p>
				</td>
			</tr>

			<!-- Logging -->
			<tr>
				<th scope="row">
					<label for="cfcc_logging"><?php 
echo  __( 'Enable Logging', 'contact-form-7-capsule-crm' ) ;
?></label>
				</th>
				<td>
					<input type="checkbox" <?php 
checked( $logging, 'on', true );
?> name="cfcc_logging" id="cfcc_logging">
					<small><?php 
echo  esc_html( CFCC_CAPSULE_CRM_LOG_FILE ) ;
?></small>
				</td>
			</tr>

		</tbody>
	</table>

	<br>
	<div id="tabs">

		<?php 

if ( $form_tags ) {
    ?>
			<div class="capsule-crm-float postbox">
				<?php 
    
    
    ?>
				<h4><?php 
    echo  __( 'Click to copy to clipboard', 'contact-form-7-capsule-crm' ) ;
    ?></h4>
				<?php 
    foreach ( $form_tags as $tag ) {
        ?>
					<button class="copy_cfcc" data-clipboard-text="[<?php 
        echo  $tag ;
        ?>]">[<?php 
        echo  $tag ;
        ?>]</button>
				<?php 
    }
    ?>
			</div>
		<?php 
}

?>

		<ul>
			<li class="active"><a href="#tabs-1"><?php 
echo  __( 'Party (Person/Organisation)', 'contact-form-7-capsule-crm' ) ;
?></a></li>
			<li><a href="#tabs-2"><?php 
echo  __( 'Opportunity', 'contact-form-7-capsule-crm' ) ;
?></a></li>
			<li><a href="#tabs-3"><?php 
echo  __( 'Case', 'contact-form-7-capsule-crm' ) ;
?></a></li>
		</ul>
		<div class="content">

		<!-- Person /Organisation -->
		<div id="tabs-1">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="cfcc_enabled_1"><?php 
echo  __( 'Enable Sending Person/Organisation', 'contact-form-7-capsule-crm' ) ;
?></label>
						</th>
						<td>
							<input <?php 
checked( esc_attr( get_post_meta( $id, 'cfcc_enabled_1', true ) ), 'on', true );
?> type="checkbox" name="cfcc_enabled_1">
						</td>
					</tr>
					</tbody>
				</table>
				<hr>
				
				<h3><?php 
echo  __( 'Party (Person/Organisation)', 'contact-form-7-capsule-crm' ) ;
?></h3>
				<table class="form-table">
					<tbody>
					<?php 
$prefix = 'cfcc_';
foreach ( Helper::get_party_fields() as $key => $val ) {
    $stored_value = get_post_meta( $id, $prefix . $key, true );
    if ( !is_array( $stored_value ) ) {
        $stored_value = esc_attr( get_post_meta( $id, $prefix . $key, true ) );
    }
    ?>
						<tr>
							<th scope="row">
								<label for="<?php 
    echo  $prefix . $key ;
    ?>"><?php 
    echo  $val['label'] ;
    ?></label>
							</th>
							<td>
							<?php 
    
    if ( $val['type'] === 'text' ) {
        echo  '<input value="' . $stored_value . '" name="' . $prefix . '' . $key . '" id="' . $prefix . '' . $key . '" type="text" class="regular-text">' ;
    } elseif ( $val['type'] === 'select' ) {
        
        if ( isset( $val['options'] ) ) {
            echo  '<select ' . (( $key === 'tags' ? 'multiple' : '' )) . ' name="' . $prefix . '' . $key . '' . (( $key === 'tags' ? '[]' : '' )) . '" id="' . $prefix . '' . $key . '">' ;
            if ( $key !== 'type' && $key !== 'tags' ) {
                echo  '<option value="">' . __( '- None -', 'contact-form-7-capsule-crm' ) . '</option>' ;
            }
            
            if ( $key === 'owner' || $key === 'tags' || $key === 'organisation' ) {
                if ( !empty($val['options']) ) {
                    foreach ( $val['options'] as $opt ) {
                        
                        if ( $key === 'tags' ) {
                            echo  '<option ' . (( is_array( $stored_value ) && in_array( $opt['name'], $stored_value ) ? 'selected' : '' )) . ' value="' . $opt['name'] . '">' . $opt['name'] . '</option>' ;
                        } elseif ( $key === 'owner' ) {
                            echo  '<option ' . selected( $stored_value, $opt['username'], false ) . ' value="' . $opt['username'] . '">' . $opt['name'] . '</option>' ;
                        } else {
                            echo  '<option ' . selected( $stored_value, $opt['id'], false ) . ' value="' . $opt['id'] . '">' . $opt['name'] . '</option>' ;
                        }
                    
                    }
                }
            } else {
                foreach ( $val['options'] as $opt ) {
                    echo  '<option ' . selected( $stored_value, $opt, false ) . ' value="' . $opt . '">' . ucfirst( $opt ) . '</option>' ;
                }
            }
            
            echo  '</select>' ;
        }
    
    } elseif ( $val['type'] === 'multi' && !empty($val['options']) ) {
        foreach ( $val['options'] as $opt ) {
            $name = $prefix . 'fields[' . $opt['id'] . ']';
            
            if ( isset( $opt['type'] ) && $opt['type'] === 'list' ) {
                echo  '<div class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                echo  '<select name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '">' ;
                echo  '<option value="">- None - </option>' ;
                foreach ( $opt['options'] as $op ) {
                    echo  '<option ' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? selected( $stored_value[$opt['id']], $op, false ) : '' )) . ' value="' . trim( $op ) . '">' . $op . '</option>' ;
                }
                echo  '</select></div>' ;
            } else {
                
                if ( isset( $opt['id'] ) && isset( $opt['name'] ) ) {
                    echo  '<div  class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                    echo  '<input value="' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? $stored_value[$opt['id']] : '' )) . '" name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '" type="text" class="regular-text">' ;
                    echo  '</div>' ;
                }
            
            }
        
        }
    }
    
    ?>
							</td>
						</tr>
					<?php 
}
?>
					</tbody>
				</table>

			</div>
		<!-- End Person/Organisation -->

		<!-- Opportunity -->
			<div id="tabs-2">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="cfcc_enabled_1"><?php 
echo  __( 'Enable Sending Opportunity', 'contact-form-7-capsule-crm' ) ;
?></label>
						</th>
						<td>
							<input <?php 
checked( esc_attr( get_post_meta( $id, 'cfcc_enabled_2', true ) ), 'on', true );
?> type="checkbox" name="cfcc_enabled_2">
						</td>
					</tr>
					</tbody>
				</table>
				<hr>
				
				<h3><?php 
echo  __( 'Opportunity', 'contact-form-7-capsule-crm' ) ;
?></h3>
				
				<table class="form-table">
					<tbody>
					<?php 
$prefix = 'cfcc2_';
foreach ( Helper::get_opp_fields() as $key => $val ) {
    $stored_value = get_post_meta( $id, $prefix . $key, true );
    ?>
						<tr>
							<th scope="row">
								<label for="<?php 
    echo  $prefix . $key ;
    ?>"><?php 
    echo  $val['label'] ;
    ?></label>
							</th>
							<td>
							<?php 
    
    if ( $val['type'] === 'text' ) {
        echo  '<input value="' . $stored_value . '" name="' . $prefix . '' . $key . '" id="' . $prefix . '' . $key . '" type="text" class="regular-text">' ;
        if ( $key === 'party' ) {
            echo  '<p>' . __( 'Leave empty to assign it to already created party in the previous tab or Write the party id in the field', 'contact-form-7-capsule-crm' ) . '</p>' ;
        }
    } elseif ( $val['type'] === 'select' ) {
        
        if ( isset( $val['options'] ) ) {
            echo  '<select ' . (( $key === 'tags' ? 'multiple' : '' )) . ' name="' . $prefix . '' . $key . '' . (( $key === 'tags' ? '[]' : '' )) . '" id="' . $prefix . '' . $key . '">' ;
            if ( $key !== 'milestone' && $key !== 'tags' ) {
                echo  '<option value="">' . __( '- None -', 'contact-form-7-capsule-crm' ) . '</option>' ;
            }
            
            if ( $key === 'owner' || $key === 'milestone' || $key === 'tags' ) {
                if ( !empty($val['options']) ) {
                    foreach ( $val['options'] as $opt ) {
                        
                        if ( $key === 'owner' ) {
                            echo  '<option ' . selected( $stored_value, $opt['username'], false ) . ' value="' . $opt['username'] . '">' . $opt['name'] . '</option>' ;
                        } elseif ( $key === 'tags' ) {
                            echo  '<option ' . (( is_array( $stored_value ) && in_array( $opt['name'], $stored_value ) ? 'selected' : '' )) . ' value="' . $opt['name'] . '">' . $opt['name'] . '</option>' ;
                        } else {
                            echo  '<option ' . selected( $stored_value, $opt['id'], false ) . ' value="' . $opt['id'] . '">' . $opt['name'] . '</option>' ;
                        }
                    
                    }
                }
            } else {
                foreach ( $val['options'] as $opt ) {
                    echo  '<option ' . selected( $stored_value, $opt, false ) . ' value="' . $opt . '">' . ucfirst( $opt ) . '</option>' ;
                }
            }
            
            echo  '</select>' ;
        }
    
    } elseif ( $val['type'] === 'multi' && !empty($val['options']) ) {
        foreach ( $val['options'] as $opt ) {
            $name = $prefix . 'fields[' . $opt['id'] . ']';
            
            if ( isset( $opt['type'] ) && $opt['type'] === 'list' ) {
                echo  '<div class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                echo  '<select name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '">' ;
                echo  '<option value="">- None - </option>' ;
                foreach ( $opt['options'] as $op ) {
                    echo  '<option ' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? selected( $stored_value[$opt['id']], $op, false ) : '' )) . ' value="' . trim( $op ) . '">' . $op . '</option>' ;
                }
                echo  '</select></div>' ;
            } else {
                
                if ( isset( $opt['id'] ) && isset( $opt['name'] ) ) {
                    echo  '<div  class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                    echo  '<input value="' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? $stored_value[$opt['id']] : '' )) . '" name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '" type="text" class="regular-text">' ;
                    echo  '</div>' ;
                }
            
            }
        
        }
    }
    
    ?>
							</td>
						</tr>
					<?php 
}
?>
					</tbody>
				</table>

			</div>
		<!-- End Opportunity -->


		<!-- Case -->
		<div id="tabs-3">
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="cfcc_enabled_1"><?php 
echo  __( 'Enable Sending Case', 'contact-form-7-capsule-crm' ) ;
?></label>
						</th>
						<td>
							<input <?php 
checked( esc_attr( get_post_meta( $id, 'cfcc_enabled_3', true ) ), 'on', true );
?> type="checkbox" name="cfcc_enabled_3">
						</td>
					</tr>
					</tbody>
				</table>
				<hr>
				
				<h3><?php 
echo  __( 'Case', 'contact-form-7-capsule-crm' ) ;
?></h3>
				<table class="form-table">
					<tbody>
					<?php 
$prefix = 'cfcc3_';
foreach ( Helper::get_case_fields() as $key => $val ) {
    $stored_value = get_post_meta( $id, $prefix . $key, true );
    ?>
						<tr>
							<th scope="row">
								<label for="<?php 
    echo  $prefix . $key ;
    ?>"><?php 
    echo  $val['label'] ;
    ?></label>
							</th>
							<td>
							<?php 
    
    if ( $val['type'] === 'text' ) {
        echo  '<input value="' . $stored_value . '" name="' . $prefix . '' . $key . '" id="' . $prefix . '' . $key . '" type="text" class="regular-text">' ;
        if ( $key === 'party' ) {
            echo  '<p>' . __( 'Leave empty to assign it to already created party in the party tab or Write the party id in the field', 'contact-form-7-capsule-crm' ) . '</p>' ;
        }
        if ( $key === 'opportunity' ) {
            echo  '<p>' . __( 'Leave empty to assign it to already created opportunity in the previous tab or Write the opportunity id in the field', 'contact-form-7-capsule-crm' ) . '</p>' ;
        }
    } elseif ( $val['type'] === 'select' ) {
        
        if ( isset( $val['options'] ) ) {
            echo  '<select ' . (( $key === 'tags' ? 'multiple' : '' )) . ' name="' . $prefix . '' . $key . '' . (( $key === 'tags' ? '[]' : '' )) . '" id="' . $prefix . '' . $key . '">' ;
            if ( $key !== 'status' && $key !== 'tags' ) {
                echo  '<option value="">' . __( '- None -', 'contact-form-7-capsule-crm' ) . '</option>' ;
            }
            
            if ( $key === 'owner' || $key === 'milestone' || $key === 'tags' ) {
                if ( !empty($val['options']) ) {
                    foreach ( $val['options'] as $opt ) {
                        
                        if ( $key === 'owner' ) {
                            echo  '<option ' . selected( $stored_value, $opt['username'], false ) . ' value="' . $opt['username'] . '">' . $opt['name'] . '</option>' ;
                        } elseif ( $key === 'tags' ) {
                            echo  '<option ' . (( is_array( $stored_value ) && in_array( $opt['name'], $stored_value ) ? 'selected' : '' )) . ' value="' . $opt['name'] . '">' . $opt['name'] . '</option>' ;
                        } else {
                            echo  '<option ' . selected( $stored_value, $opt['id'], false ) . ' value="' . $opt['id'] . '">' . $opt['name'] . '</option>' ;
                        }
                    
                    }
                }
            } else {
                foreach ( $val['options'] as $opt ) {
                    echo  '<option ' . selected( $stored_value, $opt, false ) . ' value="' . $opt . '">' . ucfirst( $opt ) . '</option>' ;
                }
            }
            
            echo  '</select>' ;
        }
    
    } elseif ( $val['type'] === 'multi' && !empty($val['options']) ) {
        foreach ( $val['options'] as $opt ) {
            $name = $prefix . 'fields[' . $opt['id'] . ']';
            
            if ( isset( $opt['type'] ) && $opt['type'] === 'list' ) {
                echo  '<div class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                echo  '<select name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '">' ;
                echo  '<option value="">- None - </option>' ;
                foreach ( $opt['options'] as $op ) {
                    echo  '<option ' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? selected( $stored_value[$opt['id']], $op, false ) : '' )) . ' value="' . trim( $op ) . '">' . $op . '</option>' ;
                }
                echo  '</select></div>' ;
            } else {
                
                if ( isset( $opt['id'] ) && isset( $opt['name'] ) ) {
                    echo  '<div  class="multi_options"><label for="' . $prefix . '' . $opt['id'] . '">' . ucfirst( $opt['name'] ) . '</label>' ;
                    echo  '<input value="' . (( !empty($stored_value) && isset( $stored_value[$opt['id']] ) ? $stored_value[$opt['id']] : '' )) . '" name="' . $name . '" id="' . $prefix . '' . $opt['id'] . '" type="text" class="regular-text">' ;
                    echo  '</div>' ;
                }
            
            }
        
        }
    }
    
    ?>
							</td>
						</tr>
					<?php 
}
?>
					</tbody>
				</table>

			</div>
		<!-- End Case -->


		</div>
	</div>
</div>


<!-- SCRIPT -->
<?php 
echo  '<script>
	jQuery("#tabs-2 input,#tabs-2 select,#tabs-3 input,#tabs-3 select").prop( "disabled", true );
</script>' ;