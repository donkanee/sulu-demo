<?php

namespace App\DataFixtures\Document;

use App\DataFixtures\ORM\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use RuntimeException;
use Sulu\Bundle\DocumentManagerBundle\DataFixtures\DocumentFixtureInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\PageBundle\Document\BasePageDocument;
use Sulu\Bundle\PageBundle\Document\HomeDocument;
use Sulu\Bundle\PageBundle\Document\PageDocument;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
use Sulu\Bundle\SnippetBundle\Snippet\DefaultSnippetManagerInterface;
use Sulu\Component\Content\Document\RedirectType;
use Sulu\Component\Content\Document\WorkflowStage;
use Sulu\Component\DocumentManager\DocumentManager;
use Sulu\Component\DocumentManager\Exception\DocumentManagerException;
use Sulu\Component\DocumentManager\Exception\MetadataNotFoundException;
use Sulu\Component\PHPCR\PathCleanup;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DocumentFixture implements DocumentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var PathCleanup
     */
    private $pathCleanup;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var DefaultSnippetManagerInterface */
    private $defaultSnippetManager;

    public function getOrder()
    {
        return 10;
    }

    /**
     * @throws DocumentManagerException
     * @throws MetadataNotFoundException
     * @throws Exception
     */
    public function load(DocumentManager $documentManager)
    {
        $pages = $this->loadPages($documentManager);
        $this->loadPagesGerman($documentManager, $pages);
        $snippet = $this->loadContactSnippet($documentManager);
        $this->loadContactSnippetGerman($documentManager, $snippet);
        $this->loadHomepage($documentManager);

        // Needed, so that a Document use by loadHomepageGerman is managed.
        $documentManager->flush();

        $this->loadHomepageGerman($documentManager);
        $this->updatePages($documentManager, AppFixtures::LOCALE_EN);
        $this->updatePages($documentManager, AppFixtures::LOCALE_DE);

        $documentManager->flush();
    }

    /**
     * @throws MetadataNotFoundException
     *
     * @return mixed[]
     */
    private function loadPages(DocumentManager $documentManager): array
    {
        $pageDataList = [
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'Artists',
                'url' => '/artists',
                'subtitle' => 'Discover our roster of talented musicians',
                'headerImage' => [
                    'id' => $this->getMediaId('artists.jpg'),
                ],
                'navigationContexts' => ['main', 'footer'],
                'structureType' => 'overview',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'Civil Literature',
                'url' => '/artists/civil-literature',
                'parent_path' => '/cmf/demo/contents/artists',
                'subtitle' => '',
                'headerImage' => [
                    'id' => $this->getMediaId('civil-literature.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Civil Literature',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>After releasing their record album in 2014, Marshall Plan spent more than one year bringing their passion for the rock music to the big concerthalls and arenas all over Great Britain – in 2015 worldwide. In this time the rockband started to grow together and wrote there second album.</p>',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>In 2010 Liam, the frontman of Civil Literature founded the band with his brother Garry, a well known guitar player and songwriter in Manchester. In 2011 Marc followed. His talent in playing the bass fullfilled the intense music vibes. Together they had one big dream: rocking the stage of the Royal Albert Hall. In 2016 they are as close as never before. With their new album, reaching records in Europe.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'I have always been a passionated songwriter. My greatest fullfillment is to touch people with my messages and to encourage them to live their dreams.',
                        'quoteReference' => 'Liam Hendrickson',
                    ],
                ],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'Coyoos',
                'url' => '/artists/coyoos',
                'parent_path' => '/cmf/demo/contents/artists',
                'subtitle' => '',
                'headerImage' => [
                    'id' => $this->getMediaId('coyoos.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Coyoos',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>After releasing their record album in 2012, Coyoos spent more than one year bringing their passion for the folk music to the big concerthalls and arenas all over the United States – in 2015 worldwide. In this time the folkband started to grow together and wrote there second album.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'Travelling and singing is all I need. Discover new places and meeting inspiring people are the experiences you never forget. It is my source of creativity and inspiration.',
                        'quoteReference' => 'Jack',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>In 2014 Jack started his music career in San Diego, California. His talent in playing the guitar made him famous in a short period of time. His one big dream: touring around the United States. In 2016 he is as close as never before – with his new album, reaching records in the US.</p>',
                    ],
                ],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'Marshall Plan',
                'url' => '/artists/marshall-plan',
                'parent_path' => '/cmf/demo/contents/artists',
                'subtitle' => '',
                'headerImage' => [
                    'id' => $this->getMediaId('marshall.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Marshall Plan',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>Releasing their record album in 2003, Marshall Plan spent more than one year bringing their passion for the rock music to the big concerthalls and arenas all over Great Britain – in 2015 worldwide. In this time the rockband started to grow together and wrote there second album.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'We love making music together and to inspire people around us with our songs. We come from a small town in the UK. It still feels unreal, to be known from Asia to the States.',
                        'quoteReference' => 'Jason Mcconkey',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>In 2003 Alex, the frontman of Marshall Plan founded the band with his best friends Bronson, Albert and Ray. Those well known guitar player and songwriter in Liverpool. In 2007 Jason followed. His talent in playing the bass fullfilled the intense music vibes. Together they had one big dream: rocking the stage in front of the Times Square in New York. In 2016 they are as close as never before. With their new album, reaching records all over the world.</p>',
                    ],
                ],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'The Bagpipes',
                'url' => '/artists/the-bagpipes',
                'parent_path' => '/cmf/demo/contents/artists',
                'subtitle' => '',
                'headerImage' => [
                    'id' => $this->getMediaId('dudelsack.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'The Bagpipes',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>In the beginning they focused on traditional and contemporary music with innovative flair, before they concetrated on there classic bagpipe music. Short after releasing their record album in 1998, The Bagpipes spent more than one year bringing their passion for the folk music to the big concerthalls and arenas all over Scottland – in 2015 worldwide. In this time the folkband started to grow together and wrote there fourth album. Soon they become the Scottish folkband of the year 2003, 2005 and 2014.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'We started our career in the streets of Glasgow. There is nothing more real and authentic then playing street music. People like you, or they don\'t and pass you. You immediately get the reaction.',
                        'quoteReference' => 'Steve Avril',
                    ],
                ],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'TJ Fury',
                'url' => '/artists/tj-fury',
                'parent_path' => '/cmf/demo/contents/artists',
                'subtitle' => '',
                'headerImage' => [
                    'id' => $this->getMediaId('tj-fury.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'TJ Fury',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>In the beginning he focused on combinations between music and Hip Hop. Today he concentrated on stong powerful Lines in the scene of Hip Hop. After releasing his record album in 2011, TJ Fury spent more than one year bringing his passion for Hip Hop to the downtown clubs all around the big cities in the United States – in 2015 worldwide. In this time TJ Fury started to record their new album. Soon they got several awards.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'We love making music. Check out our new tracks.',
                        'quoteReference' => 'TJ Fury',
                    ],
                ],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'Blog',
                'url' => '/blog',
                'subtitle' => 'We like to give you insights into what we do',
                'headerImage' => [
                    'id' => $this->getMediaId('blog.jpg'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Coming soon!',
                    ],
                ],
                'navigationContexts' => ['main'],
                'structureType' => 'default',
            ],
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => 'About Us',
                'url' => '/about',
                'subtitle' => 'We work hard, but we love what we do',
                'headerImage' => [
                    'id' => $this->getMediaId('about.png'),
                ],
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'International Talents',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<h3>International Talents was founded 1998</h3><p>From Great Britain all over the world International Talents become one of the worldwide leading music brand. With over 20 years of recorded music history, our passion for artistry in music continues today. We love to inspire young talents with all of our knowledge and experience.&nbsp;The desire to speak into the heart and soul of the listeners is what fueled the creative and strategic efforts of the label.</p>',
                    ],
                    [
                        'type' => 'quote',
                        'quote' => 'The whole experience of 20 years and a lot of knowledge come together in International Talents. We love what we do, and no day is like the one before.',
                        'quoteReference' => 'Jonathan Benett',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<p>But all this, is not possible, without a Team behind. This Team is attendable around the clock. They prepare your Event, help for your exhibition or Product Presentation. Everyone of them is an urban legend in what they do. Success is no accident. It grows with an great Team.</p>',
                    ],
                ],
                'navigationContexts' => ['main', 'footer'],
                'structureType' => 'default',
            ],
        ];

        $pages = [];

        foreach ($pageDataList as $pageData) {
            $pages[$pageData['url']] = $this->createPage($documentManager, $pageData);
        }

        return $pages;
    }

    /**
     * @param PageDocument[] $pages
     *
     * @throws MetadataNotFoundException
     */
    private function loadPagesGerman(DocumentManager $documentManager, array $pages): void
    {
        $pageDataList = [];

        /**
         * @var string
         * @var PageDocument $pageDocument
         */
        foreach ($pages as $url => $pageDocument) {
            switch ($url) {
                case '/artists':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'Musiker',
                        'url' => '/musiker',
                        'subtitle' => 'Entdecke unsere Vielfalt an talentierten Musiker',
                        'headerImage' => [
                            'id' => $this->getMediaId('artists.jpg'),
                        ],
                        'navigationContexts' => ['main', 'footer'],
                        'structureType' => 'overview',
                    ];

                    break;
                case '/artists/civil-literature':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'Civil Literature',
                        'url' => '/musiker/civil-literature',
                        'parent_path' => '/cmf/demo/contents/artists',
                        'subtitle' => '',
                        'headerImage' => [
                            'id' => $this->getMediaId('civil-literature.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'Civil Literature',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>Nach dem release ihres neuen Albums 2014, verbrachte Civil Literature mehr als ein Jahr damit, auf den großen Bühnen der riesigen Hallen in Großbritanien, ihre Leidenschaft für die Rock Musik zu teilen - und 2015 dann sogar weltweit. In dieser Zeit wuchs die Rockband noch enger zusammen und schrieb ihr drittes Album.</p>',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>Im Jahr 2010 gründete Liam, der Frontsänger der Band Civil Literature die Band mit seinem Bruder Garry, der in Manchester auch als Gitarrist und Songschreiber bekannt ist. Im Jahr 2011 folgte dann Marc. Sein Talent als Bass Spieler ergänzt sich perfekt zu der Musik die sie machten. Zusammen hatten sie einen großen Traum. Sie wollen zusammen die Bühne der Royal Albert Halle rocken. Im Jahr 2016 stehen sie vor diesem Ziel nun so kurz bevor. Vorallem deshalb, weil ihr neues Album Rekorde in ganz Europa bricht.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Ich war schon immer ein leidenschaftlicher Song Schreiber. Mein größter Wunsch ist es die Menschen zu berühren und mit meiner Botschaft ermutigen nach ihren Träumen zu leben.',
                                'quoteReference' => 'Liam Hendrickson',
                            ],
                        ],
                        'structureType' => 'default',
                    ];

                    break;
                case '/artists/coyoos':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'Coyoos',
                        'url' => '/musiker/coyoos',
                        'parent_path' => '/cmf/demo/contents/artists',
                        'subtitle' => '',
                        'headerImage' => [
                            'id' => $this->getMediaId('coyoos.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'Coyoos',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>Nach dem release ihres neuen Albums 2012, verbrachte Coyoos mehr als ein Jahr damit, auf den großen Bühnen der riesigen Hallen in den Vereinigten Staaten, ihre Leidenschaft für die Rock Musik zu teilen - und 2015 dann sogar weltweit. In dieser Zeit wuchs die Rockband noch enger zusammen und schrieb ihr drittes Album.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Neue Orte zu entdecken und inspirierende Leute kennenzulernen sind Erfahrungen, die man nie vergisst. Sie sind die Quelle meiner Kreativität und Inspiration.',
                                'quoteReference' => 'Jack',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>2014 startete Jack seine Musikkarriere in San Diego, California. Sein Talent mit der Gitarre lies ihn in kurzer Zeit bekannt werden. Sein großer Traum: In einer Tour durch die Vereinigten Staaten reisen. 2016 ist er so nah an seinem Ziel wie noch nie zuvor - mit seinem neuen Album erreichte er die Spitze der Charts in den Vereinigten Staaten.</p>',
                            ],
                        ],
                        'structureType' => 'default',
                    ];

                    break;
                case '/artists/marshall-plan':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'Marshall Plan',
                        'url' => '/musiker/marshall-plan',
                        'parent_path' => '/cmf/demo/contents/artists',
                        'subtitle' => '',
                        'headerImage' => [
                            'id' => $this->getMediaId('marshall.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'Marshall Plan',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>Nach dem Release ihres neuen Albums 2003, verbrachte Civil Literature mehr als ein Jahr damit, auf den großen Bühnen der riesigen Hallen in Großbritanien, ihre Leidenschaft für die Rock Musik zu teilen - und 2015 dann sogar weltweit. In dieser Zeit wuchs die Rockband noch enger zusammen und schrieb ihr zweites Album.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Wir lieben es, zusammen Musik zu machen und die Menschen um uns herum mit unseren Songs zu inspirieren. Wir kommen aus einem kleinen Dorf in Großbritannien. Es fühlt sich surreal an, dass wir uns einen Namen von Asien bis zu den Staaten gemacht haben.',
                                'quoteReference' => 'Jason Mcconkey',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>2003 gründete Alex, der Frontman von Marshall Plan die Band mit seinen besten freunden Albert und Ray, ein in Liverpool bekannter Gitarrenspieler und Songschreiber. 2007 folgte dann Jason. Sein Talent mit dem Bass war genau das richtige für die intensiven Vibes der Band. Sie hatten einen großen Traum zusammen: Die Bühne vor dem Times Square in New York zu rocken. 2016 sind sie so nah an ihrem Ziel wie noch nie zuvor - mit ihrem neuen Album erreichten sie die Spitze der Charts in den Vereinigten Staaten.</p>',
                            ],
                        ],
                        'structureType' => 'default',
                    ];

                    break;
                case '/artists/the-bagpipes':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'The Bagpipes',
                        'url' => '/musiker/the-bagpipes',
                        'parent_path' => '/cmf/demo/contents/artists',
                        'subtitle' => '',
                        'headerImage' => [
                            'id' => $this->getMediaId('dudelsack.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'The Bagpipes',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>In den Anfängen haben sich die Bagpipes auf traditionelle und zeitnahe Musik mit ihrem innovativen Flair konzentriert, bevor sie sich dann auf ihre klassische Dudelsackmusik stürtzten. Kurz nach der Veröffentlichung ihres Albums in 1998, haben die Bagpipes mehr als ein Jahr zusammen damit verbracht, ihre Leidenschaft auf die Bühnen und Arenen Schottlands zu bringen - in 2015 dann Weltweit. In dieser Zeit wuchs die Folkband noch enger zusammen und schrieb ihr viertes Album. Sie wurden die Schottische Folkband von den Jahren 2003, 2005 und 2014.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Unsere Karriere startete auf den Straßen von Glasgow. Es gibt nichts authentischeres als Straßenmusik. Die Menschen mögen dich, oder laufen einfach weiter. Man merkt sofort, wie die Musik ankommt.',
                                'quoteReference' => 'Steve Avril',
                            ],
                        ],
                        'structureType' => 'default',
                    ];

                    break;
                case '/artists/tj-fury':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'TJ Fury',
                        'url' => '/musiker/tj-fury',
                        'parent_path' => '/cmf/demo/contents/artists',
                        'subtitle' => '',
                        'headerImage' => [
                            'id' => $this->getMediaId('tj-fury.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'TJ Fury',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>In den Anfängen hat sich TJ Fury auf Kombinationen von zeitnaher Musik und Hip Hop fokusiert. Heute konzentriert er sich auf kraftvolle Texte in der Hip Hop Szene. Nach der Veröffentlichung seines Albums in 2011, hat TJ Fury mehr als ein Jahr damit verbracht seine Leidenschaft für Hip Hop in die Clubs der größen Städte rundum den Staaten zu bringen - in 2015 dann Weltweit. Zu dieser Zeit nahm TJ Fury sein neues Album auf. Bald wurde er für zahlreiche Auszeichnungen nominiert und gewann einige davon.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Wir lieben es, Musik zu kreieren. Hört euch unsere neuen Tracks an.',
                                'quoteReference' => 'TJ Fury',
                            ],
                        ],
                        'structureType' => 'default',
                    ];

                    break;
                case '/blog':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'Blog',
                        'url' => '/blog',
                        'subtitle' => 'Erhalten Sie einen Einblick in unsere Arbeit',
                        'headerImage' => [
                            'id' => $this->getMediaId('blog.jpg'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'Kommt bald!',
                            ],
                        ],
                        'navigationContexts' => ['main'],
                        'structureType' => 'default',
                    ];

                    break;
                case '/about':
                    $pageDataList[] = [
                        'id' => $pageDocument->getUuid(),
                        'locale' => AppFixtures::LOCALE_DE,
                        'title' => 'International Talents',
                        'url' => '/about',
                        'subtitle' => 'Wir arbeiten hart, aber lieben was wir tun',
                        'headerImage' => [
                            'id' => $this->getMediaId('about.png'),
                        ],
                        'blocks' => [
                            [
                                'type' => 'heading',
                                'heading' => 'International Talents',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<h3>International Talents wurde 1998 gegründet.</h3><p>Von Großbritanien aus wuchs International Talents über die ganze Welt zu einer der weltweit führenden Musik Marken.Wie lieben es junge Talente mit all unserem Wissen und Erfahrungen zu begleiten und inspirieren. Mit über 20 Jahren an Musik Aufnahmen, unserer Leidenschaft für die Musik Künstler geht heute weiter. Der Wunsch den Höreren und Fans ins Herz zusprechen ist die Motivation für immer neue kreative Ideen und Strategien des Labels.</p>',
                            ],
                            [
                                'type' => 'quote',
                                'quote' => 'Die ganze Erfahrung aus 20 Jahren und eine Menge Wissen kommen bei International Talents zusammen. Wir lieben was wir tun und kein Tag ist wie der zuvor.',
                                'quoteReference' => 'Jonathan Benett',
                            ],
                            [
                                'type' => 'text',
                                'text' => '<p>Aber alles zusammen wäre nicht möglich, ohne ein Team welches dahinter steht. Dieses Team ist erreichbar rund um die Uhr. Sie bereiten dein Event vor, helfen bei deiner Ausstellung oder Produkt Präsentation. Jeder von Ihnen ist eine lebende Legende in was sie tun. Erfolg passiert nicht einfach so. Er wächst vielmehr mit einem großartigen Team.</p>',
                            ],
                        ],
                        'navigationContexts' => ['main', 'footer'],
                        'structureType' => 'default',
                    ];

                    break;
            }
        }

        foreach ($pageDataList as $pageData) {
            $this->createPage($documentManager, $pageData);
        }
    }

    /**
     * @throws DocumentManagerException
     */
    private function loadHomepage(DocumentManager $documentManager): void
    {
        /** @var HomeDocument $homeDocument */
        $homeDocument = $documentManager->find('/cmf/demo/contents', AppFixtures::LOCALE_EN);

        /** @var BasePageDocument $aboutDocument */
        $aboutDocument = $documentManager->find('/cmf/demo/contents/about-us', AppFixtures::LOCALE_EN);

        /** @var BasePageDocument $headerTeaserDocument */
        $headerTeaserDocument = $documentManager->find('/cmf/demo/contents/artists/coyoos', AppFixtures::LOCALE_EN);

        $homeDocument->getStructure()->bind(
            [
                'locale' => AppFixtures::LOCALE_EN,
                'title' => $homeDocument->getTitle(),
                'url' => '/',
                'teaser' => $headerTeaserDocument->getUuid(),
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Our Label',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<h3>International Talents was founded 1998</h3><p>From Great Britain all over the world International Talents become one of the worldwide leading music brand. With over 20 years of recorded music history, our passion for artistry in music continues today. We love to inspire young talents with all of our knowledge and experience.&nbsp;The desire to speak into the heart and soul of the listeners is what fueled the creative and strategic efforts of the label.</p>',
                    ],
                    [
                        'type' => 'link',
                        'page' => $aboutDocument->getUuid(),
                        'text' => 'READ MORE',
                    ],
                ],
            ]
        );

        $documentManager->persist($homeDocument, AppFixtures::LOCALE_EN);
        $documentManager->publish($homeDocument, AppFixtures::LOCALE_EN);
    }

    /**
     * @throws DocumentManagerException
     */
    private function loadHomepageGerman(DocumentManager $documentManager): void
    {
        $documentManager->clear();

        /** @var HomeDocument $homeDocument */
        $homeDocument = $documentManager->find('/cmf/demo/contents', AppFixtures::LOCALE_DE);

        /** @var BasePageDocument $aboutDocument */
        $aboutDocument = $documentManager->find('/cmf/demo/contents/about-us', AppFixtures::LOCALE_DE);

        /** @var BasePageDocument $headerTeaserDocument */
        $headerTeaserDocument = $documentManager->find('/cmf/demo/contents/artists/coyoos', AppFixtures::LOCALE_DE);

        $homeDocument->getStructure()->bind(
            [
                'locale' => AppFixtures::LOCALE_DE,
                'title' => $homeDocument->getTitle(),
                'url' => '/',
                'teaser' => $headerTeaserDocument->getUuid(),
                'blocks' => [
                    [
                        'type' => 'heading',
                        'heading' => 'Unser Label',
                    ],
                    [
                        'type' => 'text',
                        'text' => '<h3>International Talents wurde 1998 gegründet</h3><p>Von Großbritanien aus wuchs International Talents über die ganze Welt zu einer der weltweit führenden Musik Marken.Wie lieben es junge Talente mit all unserem Wissen und Erfahrungen zu begleiten und inspirieren. Mit über 20 Jahren an Musik Aufnahmen, unserer Leidenschaft für die Musik Künstler geht heute weiter. Der Wunsch den Höreren und Fans ins Herz zusprechen ist die Motivation für immer neue kreative Ideen und Strategien des Labels.</p>',
                    ],
                    [
                        'type' => 'link',
                        'page' => $aboutDocument->getUuid(),
                        'text' => 'MEHR LESEN',
                    ],
                ],
            ]
        );

        $documentManager->persist($homeDocument, AppFixtures::LOCALE_DE);
        $documentManager->publish($homeDocument, AppFixtures::LOCALE_DE);
    }

    /**
     * @throws Exception
     */
    private function loadContactSnippet(DocumentManager $documentManager): SnippetDocument
    {
        $data = [
            'locale' => AppFixtures::LOCALE_EN,
            'title' => 'Z',
            'contact' => [
                'id' => 1,
            ],
        ];

        $snippetDocument = $this->createSnippet($documentManager, 'contact', $data);

        $this->getDefaultSnippetManager()->save('demo', 'contact', $snippetDocument->getUuid(), AppFixtures::LOCALE_EN);

        return $snippetDocument;
    }

    /**
     * @throws Exception
     */
    private function loadContactSnippetGerman(DocumentManager $documentManager, SnippetDocument $snippetDocument): void
    {
        $data = [
            'id' => $snippetDocument->getUuid(),
            'locale' => AppFixtures::LOCALE_DE,
            'title' => 'Z',
            'contact' => [
                'id' => 1,
            ],
        ];

        $snippetDocument = $this->createSnippet($documentManager, 'contact', $data);

        $this->getDefaultSnippetManager()->save('demo', 'contact', $snippetDocument->getUuid(), AppFixtures::LOCALE_DE);
    }

    /**
     * @param mixed[] $data
     *
     * @throws MetadataNotFoundException
     */
    private function createSnippet(DocumentManager $documentManager, string $structureType, array $data): SnippetDocument
    {
        $locale = isset($data['locale']) && $data['locale'] ? $data['locale'] : AppFixtures::LOCALE_EN;

        /** @var SnippetDocument $snippetDocument */
        $snippetDocument = null;

        try {
            if (!isset($data['id']) || !$data['id']) {
                throw new \OutOfBoundsException();
            }

            /** @var SnippetDocument $snippetDocument */
            $snippetDocument = $documentManager->find($data['id'], $locale);
        } catch (DocumentManagerException | \OutOfBoundsException $e) {
            /** @var SnippetDocument $snippetDocument */
            $snippetDocument = $documentManager->create('snippet');
        }

        $snippetDocument->getUuid();
        $snippetDocument->setLocale($locale);
        $snippetDocument->setTitle($data['title']);
        $snippetDocument->setStructureType($structureType);
        $snippetDocument->setWorkflowStage(WorkflowStage::PUBLISHED);
        $snippetDocument->getStructure()->bind($data);

        $documentManager->persist($snippetDocument, $locale, ['parent_path' => '/cmf/snippets']);
        $documentManager->publish($snippetDocument, $locale);

        return $snippetDocument;
    }

    /**
     * @throws DocumentManagerException
     */
    private function updatePages(DocumentManager $documentManager, string $locale): void
    {
        /** @var BasePageDocument $artistsDocument */
        $artistsDocument = $documentManager->find('/cmf/demo/contents/artists', $locale);

        $data = $artistsDocument->getStructure()->toArray();

        $data['elements'] = [
            'sortBy' => 'published',
            'sortMethod' => 'asc',
            'dataSource' => $artistsDocument->getUuid(),
        ];

        $artistsDocument->getStructure()->bind($data);

        $documentManager->persist($artistsDocument, $locale);
        $documentManager->publish($artistsDocument, $locale);
    }

    /**
     * @param mixed[] $data
     *
     * @throws MetadataNotFoundException
     */
    private function createPage(DocumentManager $documentManager, array $data): PageDocument
    {
        $locale = isset($data['locale']) && $data['locale'] ? $data['locale'] : AppFixtures::LOCALE_EN;

        if (!isset($data['url'])) {
            $url = $this->getPathCleanup()->cleanup('/' . $data['title']);
            if (isset($data['parent_path'])) {
                $url = mb_substr($data['parent_path'], mb_strlen('/cmf/demo/contents')) . $url;
            }

            $data['url'] = $url;
        }

        $extensionData = [
            'seo' => $data['seo'] ?? [],
            'excerpt' => $data['excerpt'] ?? [],
        ];

        unset($data['excerpt']);
        unset($data['seo']);

        /** @var PageDocument $pageDocument */
        $pageDocument = null;

        try {
            if (!isset($data['id']) || !$data['id']) {
                throw new \OutOfBoundsException();
            }

            /** @var PageDocument $pageDocument */
            $pageDocument = $documentManager->find($data['id'], $locale);
        } catch (DocumentManagerException | \OutOfBoundsException $e) {
            /** @var PageDocument $pageDocument */
            $pageDocument = $documentManager->create('page');
        }

        $pageDocument->setNavigationContexts($data['navigationContexts'] ?? []);
        $pageDocument->setLocale($locale);
        $pageDocument->setTitle($data['title']);
        $pageDocument->setResourceSegment($data['url']);
        $pageDocument->setStructureType($data['structureType'] ?? 'default');
        $pageDocument->setWorkflowStage(WorkflowStage::PUBLISHED);
        $pageDocument->getStructure()->bind($data);
        $pageDocument->setAuthor(1);
        $pageDocument->setExtensionsData($extensionData);

        if (isset($data['redirect'])) {
            $pageDocument->setRedirectType(RedirectType::EXTERNAL);
            $pageDocument->setRedirectExternal($data['redirect']);
        }

        $documentManager->persist(
            $pageDocument,
            $locale,
            ['parent_path' => $data['parent_path'] ?? '/cmf/demo/contents']
        );

        // Set dataSource to current page after persist as uuid is before not available
        if (isset($data['pages']['dataSource']) && '__CURRENT__' === $data['pages']['dataSource']) {
            $pageDocument->getStructure()->bind([
                'pages' => array_merge(
                    $data['pages'],
                    [
                        'dataSource' => $pageDocument->getUuid(),
                    ]
                ),
            ]);

            $documentManager->persist(
                $pageDocument,
                $locale,
                ['parent_path' => $data['parent_path'] ?? '/cmf/demo/contents']
            );
        }

        $documentManager->publish($pageDocument, $locale);

        return $pageDocument;
    }

    private function getPathCleanup(): PathCleanup
    {
        if (null === $this->pathCleanup) {
            $this->pathCleanup = $this->container->get('sulu.content.path_cleaner');
        }

        return $this->pathCleanup;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if (null === $this->entityManager) {
            $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        }

        return $this->entityManager;
    }

    private function getDefaultSnippetManager(): DefaultSnippetManagerInterface
    {
        if (null === $this->defaultSnippetManager) {
            $this->defaultSnippetManager = $this->container->get('sulu_snippet.default_snippet.manager');
        }

        return $this->defaultSnippetManager;
    }

    private function getMediaId(string $name): int
    {
        try {
            $id = $this->getEntityManager()->createQueryBuilder()
                ->from(Media::class, 'media')
                ->select('media.id')
                ->innerJoin('media.files', 'file')
                ->innerJoin('file.fileVersions', 'fileVersion')
                ->where('fileVersion.name = :name')
                ->setMaxResults(1)
                ->setParameter('name', $name)
                ->getQuery()->getSingleScalarResult();

            return (int) $id;
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(sprintf('Too many images with the name "%s" found.', $name), 0, $e);
        }
    }
}
