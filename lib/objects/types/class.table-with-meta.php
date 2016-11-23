<?php
/**
 * Table With Meta object type.
 *
 * @since 2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_Table_With_Meta_Object_Type
 */
abstract class ITE_Table_With_Meta_Object_Type extends ITE_Table_Object_Type implements ITE_Object_Type_With_Meta {

	/**
	 * @inheritDoc
	 */
	public function add_meta( $object_id, $key, $value, $unique = false ) {
		/** @var \IronBound\DB\Extensions\Meta\WithMeta $o */
		return ($o = $this->get_object_by_id( $object_id ) ) && $o->add_meta( $key, $value, $unique );
	}

	/**
	 * @inheritDoc
	 */
	public function update_meta( $object_id, $key, $value, $prev_value = '' ) {
		/** @var \IronBound\DB\Extensions\Meta\WithMeta $o */
		return ($o = $this->get_object_by_id( $object_id ) ) && $o->update_meta( $key, $value, $prev_value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $object_id, $key = '', $single = true ) {
		/** @var \IronBound\DB\Extensions\Meta\WithMeta $o */
		return ($o = $this->get_object_by_id( $object_id ) ) && $o->get_meta( $key, $single );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_meta( $object_id, $key, $value = '', $delete_all = false ) {
		/** @var \IronBound\DB\Extensions\Meta\WithMeta $o */
		return ($o = $this->get_object_by_id( $object_id ) ) && $o->delete_meta( $key, $value, $delete_all );
	}
}
