<?php

return array(
    array(
        'label' => 'Page 1',
        'uri'   => 'page1',
        'pages' => array(
            array(
                'label' => 'Page 1.1',
                'uri'   => 'page1/page1_1',
            ),
        ),
    ),
    array(
        'label' => 'Page 2',
        'uri'   => 'page2',
        'pages' => array(
            array(
                'label' => 'Page 2.1',
                'uri'   => 'page2/page2_1',
            ),
            array(
                'label' => 'Page 2.2',
                'uri'   => 'page2/page2_2',
                'pages' => array(
                    array(
                        'label' => 'Page 2.2.1',
                        'uri'   => 'page2/page2_2/page2_2_1',
                    ),
                    array(
                        'label' => 'Page 2.2.2',
                        'uri'   => 'page2/page2_2/page2_2_2',
                        'active' => true,
                    ),
                ),
            ),
        ),
    ),
);
