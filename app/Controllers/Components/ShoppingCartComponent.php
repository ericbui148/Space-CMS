<?php
namespace App\Controllers\Components;

class ShoppingCartComponent
{
	private $hash = NULL;
	
	private $model = NULL;
	
	public function __construct($model, $hash)
	{
		$this->model = $model;
		$this->hash = $hash;
		
		return $this;
	}
	
	public function clear()
	{
		$this->model
			->reset()
			->where('hash', $this->hash)
			->eraseAll();
		
		return $this;
	}
	
	public function get($key)
	{
		if ($this->has($key))
		{
			$arr = $this->model
				->reset()
				->where('t1.hash', $this->hash)
				->where('t1.key_data', $key)
				->limit(1)
				->findAll()
				->getData();
			return !empty($arr) ? $arr[0] : false;
		}
		
		return false;
	}
	
	public function getAll($hash=NULL)
	{
		return $this->model->reset()->where('t1.hash', (is_null($hash) ? $this->hash : $hash))->findAll()->getData();
	}
	
	public function has($key)
	{
		if ($this->model->reset()->where('t1.hash', $this->hash)->where('t1.key_data', $key)->findCount()->getData() == 0)
		{
			return false;
		}
		
		return true;
	}
		
	public function insert($key, $value)
	{
		$item = unserialize($key);
		$this->model->reset()->setAttributes(array(
			'stock_id' => $item['stock_id'],
			'product_id' => $item['product_id'],
			'hash' => $this->hash,
			'key_data' => $key,
			'qty' => $value
		))->insert();
		
		return $this;
	}
	
	public function isEmpty()
	{
		if ($this->model->reset()->where('t1.hash', $this->hash)->findCount()->getData() == 0)
		{
			return true;
		}
		
		return false;
	}
	
	public function remove($hashedKey)
	{
		$this->model
			->reset()
			->where('hash', $this->hash)
			->where(sprintf("MD5(`key_data`) = '%s'", $this->model->escapeStr($hashedKey)))
			->limit(1)
			->eraseAll();
		
		return $this;
	}
	
	public function transform($hash)
	{
		$cart_1 = $this->getAll();
		$cart_2 = $this->getAll($hash);
		$cart_3 = array();
		foreach ($cart_2 as $cart_item)
		{
			$cart_3[$cart_item['key_data']] = $cart_item;
		}
		
		foreach ($cart_1 as $cart_item)
		{
			if (!array_key_exists($cart_item['key_data'], $cart_3))
			{
				$this->model
					->reset()
					->where('hash', $this->hash)
					->where('key_data', $cart_item['key_data'])
					->limit(1)
					->modifyAll(array('hash' => $hash));
			} else {
				$this->model
					->reset()
					->where('hash', $hash)
					->where('key_data', $cart_item['key_data'])
					->limit(1)
					->modifyAll(array('qty' => $cart_item['qty']))
					->reset()
					->set('id', $cart_item['id'])
					->erase();
			}
		}
		
		return $this;
	}
	
	public function update($key, $value)
	{
		$this->model
			->reset()
			->where('hash', $this->hash)
			->where('key_data', $key)
			->limit(1)
			->modifyAll(array('qty' => $value));
			
		return $this;
	}
}
?>