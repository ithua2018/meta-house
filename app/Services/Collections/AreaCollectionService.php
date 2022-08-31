<?php
namespace  App\Services\Collections;
use App\Models\MongDB\Store;
use App\Services\BaseService;
use Matchory\Elasticsearch\Facades\ES;

class AreaCollectionService extends BaseService
{
    /**
     * 搜索20条数据
     * @param  string  $keyword
     *
     * @return \Matchory\Elasticsearch\Collection
     */
    public function topSearch(string $keyword)
    {
        $arr = Es::index('areas')->body([
            "query" => [
                "constant_score" => [
                    "filter" => [
                        "bool" => [
                            "filter" =>[
                                [
                                    "terms" => [
                                        "deep" =>[
                                            1,
                                            2
                                        ],
                                        "boost" =>1
                                    ]
                                ],
                                [
                                    "bool" => [
                                        "should" =>[
                                            [
                                                "match" => [
                                                    "name.ngram" => [
                                                        "query" =>$keyword,
                                                        "operator" =>"OR",
                                                        "analyzer" =>"ngramSearchAnalyzer",
                                                        "prefix_length" =>0,
                                                        "max_expansions" =>50,
                                                        "fuzzy_transpositions" =>true,
                                                        "lenient" =>false,
                                                        "zero_terms_query" =>"NONE",
                                                        "auto_generate_synonyms_phrase_query" =>true,
                                                        "boost" =>5
                                                    ]
                                                ]
                                            ],
                                            [
                                                "term" => [
                                                    "name.spy" => [
                                                        "value" =>$keyword,
                                                        "boost" =>1
                                                    ]
                                                ]
                                            ],
                                            [
                                                "wildcard" => [
                                                    "name.fpy" => [
                                                        "wildcard" => "*$keyword*",
                                                        "boost" =>0.8
                                                    ]
                                                ]
                                            ],
                                            [
                                                "match_phrase" => [
                                                    "name.fpy" => [
                                                        "query" => $keyword,
                                                        "analyzer" =>"pinyiFullSearchAnalyzer",
                                                        "slop" =>0,
                                                        "zero_terms_query" =>"NONE",
                                                        "boost" =>1
                                                    ]
                                                ]
                                            ],
                                            [
                                                "match" => [
                                                    "name" => [
                                                        "query" => $keyword,
                                                        "operator" =>"OR",
                                                        "analyzer" =>"ikSearchAnalyzer",
                                                        "prefix_length" =>0,
                                                        "max_expansions" =>50,
                                                        "minimum_should_match" =>"100%",
                                                        "fuzzy_transpositions" =>true,
                                                        "lenient" =>false,
                                                        "zero_terms_query" =>"NONE",
                                                        "auto_generate_synonyms_phrase_query" =>true,
                                                        "boost" =>1
                                                    ]
                                                ]
                                            ]
                                        ],
                                        "adjust_pure_negative" =>true,
                                        "boost" =>1
                                    ]
                                ]
                            ],
                            "adjust_pure_negative" =>true,
                            "boost" =>1
                        ]
                    ],
                    "boost" =>1
                ]
            ]
        ])->orderBy("id")->take(20)->get();
        return $arr;
    }
}
