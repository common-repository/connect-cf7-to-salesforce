<?php

namespace Procoders\CF7SF\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}

class SalesForceFields {
	public static array $objects = array(
		'persons',
		'organizations',
		'notes',
	);

	public static array $lead = array(
		[
			'id'             => 999,
			'key'            => 'title',
			'name'           => 'Title',
			'field_type'     => 'String',
			'mandatory_flag' => false
		],
		[
			'id'             => 999,
			'key'            => 'label',
			'name'           => 'Label',
			'field_type'     => 'enum',
			'mandatory_flag' => false
		]
	);

	public static array $breakFields = array(
		'Lead'          => array(
			'Id',
			'IsDeleted',
			"Name",
			"Status",
			"OwnerId",
			"IsConverted",
			"IsUnreadByOwner",
			"CreatedDate",
			"CreatedById",
			"LastModifiedDate",
			"LastModifiedById",
			"SystemModstamp",
		),
	);
}
