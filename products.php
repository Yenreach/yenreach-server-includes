<?php
require_once(LIB_PATH . DS . "database.php");

class Products
{
	private static $table_name = "products";
	protected static $db_fields = array('id', 'business_string', 'product_string', 'product_name', 'product_description', 'product_quantity', 'product_status', 'product_price', 'product_color', 'product_safety_tip', 'created_at', 'updated_at');
	public $id;
	public $business_string;
	public $product_string;
	public $product_name;
	public $product_description;
	public $product_quantity;
	public $product_price;
	public $product_color;
	public $product_status;
	public $product_safety_tip;
	public $created_at;
	public $updated_at;

	public $errors = array();

	private function check_errors()
	{
		if (empty($this->product_name)) {
			$this->errors[] = "Product Name must be provided";
		}
		if (empty($this->business_string)) {
			$this->errors[] = "business string must be provided";
		}
		if (empty($this->product_description)) {
			$this->errors[] = "product description has to be provided";
		}
	}

	public function insert()
	{
		$this->check_errors();
		if (empty($this->errors)) {
			$time = time();
			if (empty($this->created_at)) {
				$this->created_at = $time;
			}
			$this->updated_at = $time;
			if ($this->save()) {
				if (empty($this->product_string)) {
					$string = $this->id . $time;
					$this->product_string = sha1($string);
					$this->save();
				}
				return true;
			} else {
				$this->errors[] = "Business details was not saved";
				return false;
			}
		} else {
			return false;
		}
	}

	// Common Database Methods
	protected function attributes()
	{
		// return an array of attributes and their values
		$attributes = array();
		foreach (self::$db_fields as $field) {
			if (property_exists($this, $field)) {
				$attributes[$field] = $this->$field;
			}
		}
		return $attributes;
	}

	protected function sanitized_attributes()
	{
		global $database;
		$clean_attributes = array();
		// sanitize the value before submitting
		// Note: does not alter the actual value of each attribute
		foreach ($this->attributes() as $key => $value) {
			$clean_attributes[$key] = $database->escape_value($value);
		}
		return $clean_attributes;
	}

	private function has_attribute($attribute)
	{
		$object_vars = $this->attributes();
		return array_key_exists($attribute, $object_vars);
	}

	private static function instantiate($record)
	{
		$object = new self;

		foreach ($record as $attribute => $value) {
			if ($object->has_attribute($attribute)) {
				$object->$attribute = formatString(html_entity_decode($value));
			}
		}
		return $object;
	}

	public static function find_by_sql($sql = "")
	{
		global $database;
		$result_set = $database->query($sql);
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
			$object_array[] = self::instantiate($row);
		}
		return $object_array;
	}

	public static function find_all($per_page = 0, $offset = 0)
	{
		global $database;
		$sql = "SELECT * FROM " . self::$table_name;
		if ($per_page > 0) {
			$sql .= " LIMIT {$per_page} OFFSET {$offset}";
		}
		return self::find_by_sql($sql);
	}

	public static function find_active_products($per_page = 0, $offset = 0, $search = "")
	{
		global $database;
		if ($search != "") {
			$sql = "SELECT * FROM " . self::$table_name . " WHERE product_status=1 AND (product_name LIKE '%{$search}%' OR product_description LIKE '%{$search}%' OR product_color LIKE '%{$search}%' OR product_safety_tip LIKE '%{$search}%' OR product_price LIKE '%{$search}%') ORDER BY created_at ASC";
		} else {
			$sql = "SELECT * FROM " . self::$table_name . " WHERE product_status=1 ORDER BY created_at ASC";
		}
		// $sql = "SELECT * FROM ".self::$table_name." WHERE product_status=1 ORDER BY created_at ASC";
		if ($per_page > 0) {
			$sql .= " LIMIT {$per_page} OFFSET {$offset}";
		}
		return self::find_by_sql($sql);
	}

	public static function find_by_user($user)
	{
		return self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE user='{$user}' AND activation=1 ORDER BY datecreated");
	}

	public static function find_by_business_string($string)
	{
		return self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE business_string='{$string}'");
	}

	public static function find_by_id($id = 0)
	{
		global $database;
		$result_array = self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
	}

	public static function delete_filepath($id = 0)
	{
		global $database;
		$result_array = self::find_by_sql("UPDATE " . self::$table_name . " SET filepath = '' WHERE id={$id}");
		return !empty($result_array) ? array_shift($result_array) : false;
	}

	// UPDATE `businessphotos` SET `filepath` = '' WHERE `businessphotos`.`id` = 25

	public static function find_by_product_string($string = "")
	{
		global $database;
		$result_array = self::find_by_sql("SELECT * FROM " . self::$table_name . " WHERE product_string='{$string}' LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
	}

	public static function count_all()
	{
		global $database;
		$sql = "SELECT COUNT(*) FROM " . self::$table_name;
		$result_set = $database->query($sql);
		$row = $database->fetch_array($result_set);
		return array_shift($row);
	}

	public static function count_active_product($search = "")
	{
		global $database;
		$sql = "SELECT COUNT(*) FROM " . self::$table_name . " WHERE product_status=1 AND (product_name LIKE '%{$search}%' OR product_description LIKE '%{$search}%' OR product_color LIKE '%{$search}%' OR product_safety_tip LIKE '%{$search}%' OR product_price LIKE '%{$search}%')";
		$result_set = $database->query($sql);
		$row = $database->fetch_array($result_set);
		return array_shift($row);
	}

	public function create()
	{
		global $database;
		$attributes = $this->sanitized_attributes();
		$sql = "INSERT INTO " . self::$table_name . " (";
		$sql .= join(", ", array_keys($attributes));
		$sql .= ") VALUES ('";
		$sql .= join("', '", array_values($attributes));
		$sql .= "')";
		if ($database->query($sql)) {
			$this->id = $database->insert_id();
			return true;
		} else {
			return false;
		}
	}

	public function update()
	{
		global $database;
		$attributes = $this->sanitized_attributes();
		$attribute_pairs = array();
		foreach ($attributes as $key => $value) {
			$attribute_pairs[] = "{$key}='{$value}'";
		}
		$sql = "UPDATE " . self::$table_name . " SET ";
		$sql .= join(", ", $attribute_pairs);
		$sql .= " WHERE id=" . $database->escape_value($this->id);
		$sql .= " LIMIT 1";
		$result = $database->query($sql);
		//return ($database->affected_rows() == 1) ? true : false;
		return $result ? true : false;
	}

	public function save()
	{
		return isset($this->id) ? $this->update() : $this->create();
	}

	public function delete()
	{
		global $database;
		$sql = "DELETE FROM " . self::$table_name;
		$sql .= " WHERE id=" . $database->escape_value($this->id);
		$sql .= " LIMIT 1";
		$database->query($sql);
		return ($database->affected_rows() == 1) ? true : false;
	}
}
