<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class ProductsController extends Controller
{
	/**
	 * Get product count
	 *
	 * @param object $client	Shopify REST client
	 *
	 * @return json
	 */	
	public function get_product_count($client)
	{
		$result = $client->get('products/count');
	    return response($result->getDecodedBody());
	}

	/**
	 * Update product
	 *
	 * @param object $client	Shopify REST client
	 *
	 * @return json
	 */	
	public function update_product($client)
	{
		$queryString = <<<QUERY
			{
				products (first: 2) {
					edges {
						node {
							id
							title
						}
					}
				}
			}
		QUERY;
		$response = $client->query($queryString);

		$product = $response->getDecodedBody()['data']['products']['edges'][1]['node'];

		$product_id = $product['id'];
		$product_title = $product['title'];

		if (str_contains($product_title, ' -- ')) {
			$product_title = explode(' -- ', $product_title)[0];
		}

		$mutation = $client->query(
			[
				"query" => <<<'QUERY'
					mutation productUpdate($input: ProductInput!) {
						productUpdate(input: $input) {
							product {
								id
								title
							}
						}
					}
				QUERY,
				"variables" => [
					"input" => [
						"id" => $product_id,
						"title" => $product_title." -- ".date("G:i:s")
					]
				]
			]
		);
		return response($mutation->getDecodedBody()['data']['productUpdate']['product']);		
	}

}