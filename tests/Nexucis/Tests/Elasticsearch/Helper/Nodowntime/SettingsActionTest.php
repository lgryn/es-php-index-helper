<?php

namespace Nexucis\Tests\Elasticsearch\Helper\Nodowntime;

class SettingsActionTest extends AbstractIndexHelperTest
{


    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsEmpty()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->addSettings($aliasSrc, array());
    }

    /**
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     */
    public function testAddSettingsNull()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->createIndex($aliasSrc);

        self::$HELPER->addSettings($aliasSrc, null);
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testAddSettingsIndexNotFoundException()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->addSettings($aliasSrc, null);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testAddSettingsBasicData($alias)
    {
        self::$HELPER->createIndex($alias);
        $settings = [
            'analysis' => [
                'filter' => [
                    'shingle' => [
                        'type' => 'shingle'
                    ]
                ],
                'char_filter' => [
                    'pre_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                        'replacement' => '~$1 $2'
                    ],
                    'post_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                        'replacement' => '$1 ~$2'
                    ]
                ],
                'analyzer' => [
                    'reuters' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'stop', 'kstem']
                    ]
                ]
            ]
        ];

        // we need to wait a moment because ElasticSearch need to synchronize something before we can close a creating index.
        // ElasticSearch Issue  : https://github.com/elastic/elasticsearch/issues/3313
        // Idea to improve this workaround : use _cat/shards endpoint to get the shard status
        sleep(2);
        self::$HELPER->addSettings($alias, $settings);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_1));

        $resultSettings = self::$HELPER->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsEmpty($alias)
    {
        self::$HELPER->createIndex($alias);

        self::$HELPER->updateSettings($alias, array());

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', self::$HELPER->getSettings($alias)));
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsNull($alias)
    {
        self::$HELPER->createIndex($alias);

        self::$HELPER->updateSettings($alias, null);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));
        $this->assertFalse(array_key_exists('analysis', self::$HELPER->getSettings($alias)));
    }

    /**
     * @expectedException \Nexucis\Elasticsearch\Helper\Nodowntime\Exceptions\IndexNotFoundException
     */
    public function testUpdateSettingsIndexNotFound()
    {
        $aliasSrc = 'myindextest';
        self::$HELPER->updateSettings($aliasSrc, array());
    }

    /**
     * @dataProvider aliasDataProvider
     */
    public function testUpdateSettingsBasicData($alias)
    {
        $settings = [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'filter' => [
                    'shingle' => [
                        'type' => 'shingle'
                    ]
                ],
                'char_filter' => [
                    'pre_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                        'replacement' => '~$1 $2'
                    ],
                    'post_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                        'replacement' => '$1 ~$2'
                    ]
                ],
                'analyzer' => [
                    'reuters' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'stop', 'kstem']
                    ]
                ]
            ]
        ];
        self::$HELPER->createIndex($alias);

        self::$HELPER->updateSettings($alias, $settings);

        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));

        $resultSettings = self::$HELPER->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);
    }

    public function testUpdateSettingsWithIndexNotEmpty()
    {
        $settings = [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
            'analysis' => [
                'filter' => [
                    'shingle' => [
                        'type' => 'shingle'
                    ]
                ],
                'char_filter' => [
                    'pre_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '(\\w+)\\s+((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\b',
                        'replacement' => '~$1 $2'
                    ],
                    'post_negs' => [
                        'type' => 'pattern_replace',
                        'pattern' => '\\b((?i:never|no|nothing|nowhere|noone|none|not|havent|hasnt|hadnt|cant|couldnt|shouldnt|wont|wouldnt|dont|doesnt|didnt|isnt|arent|aint))\\s+(\\w+)',
                        'replacement' => '$1 ~$2'
                    ]
                ],
                'analyzer' => [
                    'reuters' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'stop', 'kstem']
                    ]
                ]
            ]
        ];

        $alias = 'financial';
        // create index with some contents
        $this->loadFinancialIndex($alias);
        $mappings = self::$HELPER->getMappings($alias);

        self::$HELPER->updateSettings($alias, $settings, true);
        $this->assertTrue(self::$HELPER->existsIndex($alias));
        $this->assertTrue(self::$HELPER->existsIndex($alias . self::$HELPER::INDEX_NAME_CONVENTION_2));

        $resultSettings = self::$HELPER->getSettings($alias);
        $this->assertTrue(array_key_exists('analysis', $resultSettings));
        $this->assertEquals($settings['analysis'], $resultSettings['analysis']);
        $this->assertEquals($settings['number_of_shards'], $resultSettings['number_of_shards']);
        $this->assertEquals($settings['number_of_replicas'], $resultSettings['number_of_replicas']);

        $this->assertTrue($this->countDocuments($alias) > 0);
        $this->assertEquals($mappings, self::$HELPER->getMappings($alias));
    }
}