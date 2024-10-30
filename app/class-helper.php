<?php
namespace CFCC_CAPSULE_CRM;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
class Helper {

	public function get_apitoken() {
		return get_option( 'cfcc_capsule_api_token' );
	}

	public static function request( $base, $body = array(), $method = 'POST', $version = 'v2' ) {
		if ( ( new self() )->get_apitoken() == '' ) {
			return;
		}

		$args     = array(
			'method'  => $method,
			'headers' => array(
				'Content-Type'  => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . ( new self() )->get_apitoken(),
			),
			'body'    => $body,
		);
		$url      = 'https://api.capsulecrm.com/api/' . $version . '/' . $base;
		$response = wp_remote_request( $url, $args );
		$status   = wp_remote_retrieve_response_code( $response );
		$body     = json_decode( wp_remote_retrieve_body( $response ) );
		if ( $status === 200 || $status === 201 ) {
			return array(
				'success' => true,
				'data'    => $body,
			);
		}
		return array(
			'success' => false,
			'data'    => $body,
		);
	}


	public static function get_party_by_id( $id ) {
		$response = ( new self() )->request( 'parties/' . $id, array(), 'GET' )['data'];
		return $response;
	}

	public function get_owners() {
		$owners = get_transient( '_cfcc_owners' );
		if ( ! empty( $owners ) ) {
			return $owners;}
		$response = self::request( 'users', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$users = $response->users;
			if ( ! empty( $users ) ) {
				foreach ( $users as $user ) {
					$final[] = array(
						'id'       => $user->id,
						'username' => $user->username,
						'name'     => $user->name,
					);
				}
			}
		}
		set_transient( '_cfcc_owners', $final );
		return $final;
	}

	public function get_milestones() {
		$milestones = get_transient( '_cfcc_milestones' );
		if ( ! empty( $milestones ) ) {
			return $milestones;}
		$response = self::request( 'milestones', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$milestones = $response->milestones;
			if ( ! empty( $milestones ) ) {
				foreach ( $milestones as $mile ) {
					$final[] = array(
						'id'   => $mile->id,
						'name' => $mile->name,
					);
				}
			}
		}
		set_transient( '_cfcc_milestones', $final );
		return $final;
	}

	public function get_opp_tags() {
		$opp_tags = get_transient( '_cfcc_opp_tags' );
		if ( ! empty( $opp_tags ) ) {
			return $opp_tags;}
		$response = self::request( 'opportunities/tags', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$tags = $response->tags;
			if ( ! empty( $tags ) ) {
				foreach ( $tags as $tag ) {
					$final[] = array(
						'id'   => $tag->id,
						'name' => $tag->name,
					);
				}
			}
		}
		set_transient( '_cfcc_opp_tags', $final );
		return $final;
	}


	public function get_case_tags() {
		$case_tags = get_transient( '_cfcc_case_tags' );
		if ( ! empty( $case_tags ) ) {
			return $case_tags;}
		$response = self::request( 'kases/tags', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$tags = $response->tags;
			if ( ! empty( $tags ) ) {
				foreach ( $tags as $tag ) {
					$final[] = array(
						'id'   => $tag->id,
						'name' => $tag->name,
					);
				}
			}
		}
		set_transient( '_cfcc_case_tags', $final );
		return $final;
	}

	public function get_person_tags() {
		$person_tags = get_transient( '_cfcc_person_tags' );
		if ( ! empty( $person_tags ) ) {
			return $person_tags;}
		$response = self::request( 'parties/tags', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$tags = $response->tags;
			if ( ! empty( $tags ) ) {
				foreach ( $tags as $tag ) {
					$final[] = array(
						'id'   => $tag->id,
						'name' => $tag->name,
					);
				}
			}
		}
		set_transient( '_cfcc_person_tags', $final );
		return $final;
	}

	public function get_organizations() {
		$party_orgs = get_transient( '_cfcc_party_orgs' );
		if ( ! empty( $party_orgs ) ) {
			return $party_orgs;}
		$data     = array(
			'filter' => array(
				'conditions' => array(
					array(
						'field'    => 'type',
						'operator' => 'is',
						'value'    => 'organisation',
					),
				),
			),
		);
		$obj      = json_decode( json_encode( $data ) );
		$response = self::request( 'parties/filters/results', wp_json_encode( $obj ), 'POST' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$parties = $response->parties;
			if ( ! empty( $parties ) ) {
				foreach ( $parties as  $part ) {
					$final[] = array(
						'id'   => $part->id,
						'name' => $part->name,
					);
				}
			}
		}
		set_transient( '_cfcc_party_orgs', $final );
		return $final;
	}


	public function get_opp_custom_fields() {
		$opp_custom_fields = get_transient( '_cfcc_opp_custom_fields' );

		if ( ! empty( $opp_custom_fields ) ) {
			return $opp_custom_fields;}
		$response = self::request( 'opportunities/fields/definitions', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$definitions = $response->definitions;
			if ( ! empty( $definitions ) ) {
				foreach ( $definitions as $key => $def ) {
					$final[ $key ] = array(
						'id'   => $def->id,
						'name' => $def->name,
						'type' => $def->type,
					);

					if ( property_exists( $def, 'options' ) ) {
						$final[ $key ]['options'] = $def->options;
					}
				}
			}
		}
		set_transient( '_cfcc_opp_custom_fields', $final );
		return $final;
	}

	public function get_case_custom_fields() {
		$case_custom_fields = get_transient( '_cfcc_case_custom_fields' );

		if ( ! empty( $case_custom_fields ) ) {
			return $case_custom_fields;}
		$response = self::request( 'kases/fields/definitions', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$definitions = $response->definitions;
			if ( ! empty( $definitions ) ) {
				foreach ( $definitions as $key => $def ) {
					$final[ $key ] = array(
						'id'   => $def->id,
						'name' => $def->name,
						'type' => $def->type,
					);

					if ( property_exists( $def, 'options' ) ) {
						$final[ $key ]['options'] = $def->options;
					}
				}
			}
		}
		set_transient( '_cfcc_case_custom_fields', $final );
		return $final;
	}

	public function get_person_custom_fields() {
		$person_custom_fields = get_transient( '_cfcc_person_custom_fields' );
		if ( ! empty( $person_custom_fields ) ) {
			return $person_custom_fields;}
		$response = self::request( 'parties/fields/definitions', array(), 'GET' )['data'];
		$final    = array();
		if ( ! is_array( $response ) ) {
			$definitions = $response->definitions;
			if ( ! empty( $definitions ) ) {
				foreach ( $definitions as $key => $def ) {
					$final[ $key ] = array(
						'id'   => $def->id,
						'name' => $def->name,
						'type' => $def->type,
					);

					if ( property_exists( $def, 'options' ) ) {
						$final[ $key ]['options'] = $def->options;
					}
				}
			}
		}
		set_transient( '_cfcc_person_custom_fields', $final );
		return $final;
	}

	public static function get_opp_field_by_id( $id ) {
		$response = ( new self() )->get_opp_custom_fields();
		$key      = array_search( $id, array_column( $response, 'id' ) );
		return $response[ $key ]['name'];
	}


	public static function get_case_field_by_id( $id ) {
		$response = ( new self() )->get_case_custom_fields();
		$key      = array_search( $id, array_column( $response, 'id' ) );
		return $response[ $key ]['name'];
	}

	public static function get_person_field_by_id( $id ) {
		$response = ( new self() )->get_person_custom_fields();
		$key      = array_search( $id, array_column( $response, 'id' ) );
		return $response[ $key ]['name'];
	}


	private function get_string_between( $form_id, $string, $post ) {
		$cf_post   = \WPCF7_ContactForm::get_instance( $form_id );
		$form_tags = $cf_post->collect_mail_tags();
		if ( empty( $form_tags ) ) {
			return;}
		foreach ( $form_tags as $tag ) {
			$search_tag = '[' . $tag . ']';
			if ( strpos( $string, $search_tag ) !== false ) {
				if ( isset( $post[ $tag ] ) ) {
					$string = str_replace( $search_tag, $post[ $tag ], $string );
				}
			}
		}
		return $string;
	}


	public static function load_form_value( $form_id, $val, $post, $key = '' ) {
		if ( $val == '' ) {
			return;}
		$value = ( new self() )->get_string_between( $form_id, $val, $post );

		if ( $key === 'phoneNumbers' ) {
			return array(
				array( 'number' => esc_attr( $value ) ),
			);
		} elseif ( $key === 'emailAddresses' ) {
			return array(
				array( 'address' => esc_attr( $value ) ),
			);
		}

		return esc_attr( $value );
	}

	public static function log( $form_id, $data ) {
		$logging = esc_attr( get_post_meta( $form_id, 'cfcc_logging', true ) );
		if ( $logging === 'on' ) {
			try {
				$directory = wp_mkdir_p( CFCC_CAPSULE_CRM_PATH_DIR . '/logs/' );
				$message   = '';
				if ( ! empty( $data ) ) {
					foreach ( $data->errors as $val ) {
						$message .= '[' . gmdate( 'Y-m-d H:i:s' ) . ']';
						$message .= "\n" . 'Form ID ' . $form_id;
						$message .= "\n" . 'Message ' . $val->message;
						$message .= "\n" . 'Resource ' . $val->resource;
						$message .= "\n" . 'Field ' . $val->field;
						$message .= "\n";
					}
				}
				if ( $directory == true ) {
					error_log( $message . PHP_EOL, 3, CFCC_CAPSULE_CRM_LOG_FILE );
				}
			} catch ( \Exception $exception ) {

			}
		}
	}

	public static function sanitize_array( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}


	public static function get_case_fields() {
		return array(
			'party'       => array(
				'label' => __( 'The main contact for this case (Required)', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'name'        => array(
				'label' => __( 'The name of this case (Required)', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'description' => array(
				'label' => __( 'The description of this case', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'owner'       => array(
				'label'   => __( 'The owner of this case', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_owners(),
			),
			'status'      => array(
				'label'   => __( 'The status of this case', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => array(
					'OPEN',
					'CLOSED',
				),
			),
			'opportunity' => array(
				'label' => __( 'The opportunity to link this case to', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'name'        => array(
				'label' => __( 'The name of this case', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'name'        => array(
				'label' => __( 'The name of this case', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'tags'        => array(
				'label'   => __( 'Tags', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_case_tags(),
			),
			'fields'      => array(
				'label'   => __( 'Custom Fields', 'contact-form-7-capsule-crm' ),
				'type'    => 'multi',
				'options' => ( new self() )->get_case_custom_fields(),
			),

		);
	}

	public static function get_opp_fields() {
		return array(
			'party'           => array(
				'label' => __( 'The main contact for this opportunity (Required)', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'name'            => array(
				'label' => __( 'The name of this opportunity (Required)', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'description'     => array(
				'label' => __( 'Description of the opportunity', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'owner'           => array(
				'label'   => __( 'Owner', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_owners(),
			),
			'milestone'       => array(
				'label'   => __( 'Milestone (Required)', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_milestones(),
			),
			'value'           => array(
				'label' => __( 'Value', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'expectedCloseOn' => array(
				'label' => __( 'The expected close date of this opportunity', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'probability'     => array(
				'label' => __( 'Probability', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'durationBasis'   => array(
				'label'   => __( 'The time unit used by the duration field', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => array(
					'FIXED',
					'HOUR',
					'DAY',
					'WEEK',
					'MONTH',
					'QUARTER',
					'YEAR',
				),
			),
			'duration'        => array(
				'label' => __( 'The duration of this opportunity', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'closedOn'        => array(
				'label' => __( 'The date this opportunity was closed', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'tags'            => array(
				'label'   => __( 'Tags', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_opp_tags(),
			),
			'fields'          => array(
				'label'   => __( 'Custom Fields', 'contact-form-7-capsule-crm' ),
				'type'    => 'multi',
				'options' => ( new self() )->get_opp_custom_fields(),
			),
		);
	}


	public static function get_party_fields() {
		return array(
			'type'           => array(
				'label'   => __( 'Party Type (Required)', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => array(
					'person',
					'organization',
				),
			),
			'firstName'      => array(
				'label' => __( 'First Name', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'lastName'       => array(
				'label' => __( 'Last Name', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'title'          => array(
				'label'   => __( 'Title', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => array(
					'Mr',
					'Master',
					'Mrs',
					'Miss',
					'Ms',
					'Dr',
					'Prof',
				),
			),
			'jobTitle'       => array(
				'label' => __( 'Job Title', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'organisation'   => array(
				'label'   => __( 'Organisation', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_organizations(),
			),

			'name'           => array(
				'label' => __( 'Organisation Name', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'about'          => array(
				'label' => __( 'About', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'street'         => array(
				'label' => __( 'Street', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'city'           => array(
				'label' => __( 'City', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'state'          => array(
				'label' => __( 'State', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'country'        => array(
				'label' => __( 'Country', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'zip'            => array(
				'label' => __( 'Zip Code', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'phoneNumbers'   => array(
				'label' => __( 'Phone Number', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

			'emailAddresses' => array(
				'label' => __( 'Email Address', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'tags'           => array(
				'label'   => __( 'Tags', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_person_tags(),
			),
			'owner'          => array(
				'label'   => __( 'Owner', 'contact-form-7-capsule-crm' ),
				'type'    => 'select',
				'options' => ( new self() )->get_owners(),
			),
			'fields'         => array(
				'label'   => __( 'Custom Fields', 'contact-form-7-capsule-crm' ),
				'type'    => 'multi',
				'options' => ( new self() )->get_person_custom_fields(),
			),
			'url'            => array(
				'label' => __( 'Website URL', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'skype'          => array(
				'label' => __( 'Skype', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'twitter'        => array(
				'label' => __( 'Twitter', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'linked_in'      => array(
				'label' => __( 'Linkedin', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'facebook'       => array(
				'label' => __( 'Facebook', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'xing'           => array(
				'label' => __( 'Xing', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'feed'           => array(
				'label' => __( 'Feed', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'google_plus'    => array(
				'label' => __( 'Google Plus', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'flickr'         => array(
				'label' => __( 'Flickr', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'github'         => array(
				'label' => __( 'Github', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'youtube'        => array(
				'label' => __( 'Youtube', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'instagram'      => array(
				'label' => __( 'Instagram', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'skype'          => array(
				'label' => __( 'Skype', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),
			'pinterest'      => array(
				'label' => __( 'Pinterest', 'contact-form-7-capsule-crm' ),
				'type'  => 'text',
			),

		);
	}

}
