<?php

namespace App\Support\Search;

use Elastic\ScoutDriverPlus\Builders\BoolQueryBuilder;
use Elastic\ScoutDriverPlus\Support\Query;

class ElasticsearchHelper
{
    /**
     * Build the Elasticsearch bool query for a product search.
     */
    public static function buildQuery(ProductSearchParams $params): BoolQueryBuilder
    {
        $query = Query::bool();

        if ($params->search) {
            $query->must(
                Query::multiMatch()
                    ->fields(['name', 'description', 'category.name'])
                    ->query($params->search)
                    ->fuzziness('AUTO')
            );
        } else {
            $query->must(Query::matchAll());
        }

        if ($params->categoryId !== null) {
            $query->filter(
                Query::term()->field('category_id')->value((string) $params->categoryId)
            );
        }

        if ($params->hasStock) {
            $query->filter(Query::range()->field('stock')->gt(0));
        }

        if ($params->minPrice !== null) {
            $query->filter(Query::range()->field('price')->gte($params->minPrice));
        }

        if ($params->maxPrice !== null) {
            $query->filter(Query::range()->field('price')->lte($params->maxPrice));
        }

        return $query;
    }
}
