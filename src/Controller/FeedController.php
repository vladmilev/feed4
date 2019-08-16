<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Feed;
use App\Entity\Offer;
use App\Entity\Apartment;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class FeedController extends AbstractController
{
    public function index(Request $request, SerializerInterface $serializer)
    {
        // Вспомогательная функция заполнения объекта Offer данными из записи фида в виде массива
        function setOffer_fromArray( Offer $obj, Array $arr, int $feed_id, string $internal_id ) {
            $obj->setExternalId( $feed_id );
            $obj->setInternalId( $internal_id );
            $obj->setCountry( array_key_exists('country', $arr['location']) ? $arr['location']['country'] : '' );
            $obj->setRegion( array_key_exists('region', $arr['location']) ? $arr['location']['region'] : '' );
            $obj->setLocalityName( array_key_exists('locality-name', $arr['location']) ? $arr['location']['locality-name'] : '' );
            $obj->setSubLocalityName( array_key_exists('sub-locality-name', $arr['location']) ? $arr['location']['sub-locality-name'] : '' );
            $obj->setAddress( array_key_exists('address', $arr['location']) ? $arr['location']['address'] : '' );
            $obj->setPriceValue( array_key_exists('price', $arr) ? $arr['price']['value'] : 0 );
            $obj->setAreaValue( array_key_exists('area', $arr) ? $arr['area']['value'] : 0 );
        }

        // Вспомогательная функция поиска изменений в данных объекта Offer и записи фида в виде массива
        function isChanged( Offer $obj, Array $arr ) {
            if (array_key_exists('country', $arr['location'])&&($obj->getCountry() != $arr['location']['country']))  return true;
            if (array_key_exists('region', $arr['location'])&&($obj->getRegion() != $arr['location']['region']))  return true;
            if (array_key_exists('locality-name', $arr['location'])&&($obj->getLocalityName() != $arr['location']['locality-name']))  return true;
            if (array_key_exists('sub-locality-name', $arr['location'])&&($obj->getSubLocalityName() != $arr['location']['sub-locality-name']))  return true;
            if (array_key_exists('address', $arr['location'])&&($obj->getAddress() != $arr['location']['address']))  return true;
            if (array_key_exists('price', $arr)&&($obj->getPriceValue() != $arr['price']['value']))  return true;
            if (array_key_exists('area', $arr)&&($obj->getAreaValue() != $arr['area']['value']))  return true;
            return false;
        }

        $feed = new Feed();
        $feed->setNameUrl('http://crm.alfa-n.recrm.ru/export/16/yandex.xml');
        //http://learnwords.ru/test/yatest.xml

        $form = $this->createFormBuilder($feed)
                    ->add('name_url', UrlType::class,
                        ['label' => 'Ссылка на фид: ', 'required' => true, 'attr' =>['style'=>'width:400px;'] ])
                    ->add('save', SubmitType::class, ['label' => 'Загрузить'])
                    ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $now= new \DateTime('now');
            //$enter_url= $request->request->get('name_url');
            $feed= $form->getData();
            $enter_url= $feed->getNameUrl();
            $feed_id= 0;

            // Создаем новый или обновляем фид, получаем external_id в переменную $feed_id
            $entityManager = $this->getDoctrine()->getManager();
            $repository = $entityManager->getRepository(Feed::class);
            $feed_exist = $repository->findOneBy(['name_url' => $enter_url]);

            if (!$feed_exist) {
                $new_feed = new Feed();
                $new_feed->setNameUrl($enter_url);
                $new_feed->setCreated( $now );
                $new_feed->setUpdated( $now );

                $entityManager->persist($new_feed);
                $entityManager->flush();

                $feed_id = $new_feed->getId();
            } else {
                $feed_id = $feed_exist->getId();
                $feed_exist->setUpdated( $now );

                $entityManager->persist($feed_exist);
                $entityManager->flush();
            }

            // Скачиваем указанный фид и формируем объекты
            $data = file_get_contents($enter_url);

            $apartments = $serializer->deserialize($data, Apartment::class, 'xml');

            $offers= $apartments->getOffer();
            $repository_offer = $entityManager->getRepository(Offer::class);
            $count_all = count($offers);
            $count_new = 0;
            $count_upd = 0;
            $count_del = 0;
            $str_for_delete_query = "";

            for($i=0; $i<$count_all; $i++) {
                $offer_internal_id = trim($offers[$i]['@internal-id']);
                if ($i>0) $str_for_delete_query.= ",";
                $str_for_delete_query.= "'".$offer_internal_id."'";
                $offer_exist = $repository_offer->findOneBy([
                    'external_id' => $feed_id,
                    'internal_id' => $offer_internal_id
                ]);
                //$oper=" - ";
                if (!$offer_exist) {
                    $new_offer = new Offer();
                    setOffer_fromArray( $new_offer, $offers[$i], $feed_id, $offer_internal_id );
                    $new_offer->setCreated( $now );
                    $new_offer->setUpdated( $now );

                    $entityManager->persist($new_offer);
                    $entityManager->flush();
                    //$oper= "new";
                    $count_new++;
                } else {
                    // обновляем только если есть отличия
                    if (isChanged( $offer_exist, $offers[$i] )) {
                        setOffer_fromArray( $offer_exist, $offers[$i], $feed_id, $offer_internal_id );
                        $offer_exist->setUpdated( $now );

                        $entityManager->persist($offer_exist);
                        $entityManager->flush();
                        //$oper= "changed";
                        $count_upd++;
                    }
                }
                //$str.= "<p>Offer-".$i." (".$oper.")<br>";
                //$str.= "internal-id: ".$offers[$i]['@internal-id']."<br>";
                //$str.= "</p>";
                //$str.="<pre>".print_r($offers[$i])."</pre>";
            }
            // Удаление записей
            $qb = $entityManager->createQueryBuilder();
            $qb->delete('App\Entity\offer','o')
                ->where('o.external_id = '.$feed_id)
                ->andWhere('o.internal_id not in ('.$str_for_delete_query.')');
            $count_del = $qb->getQuery()->execute();

            //return new Response('<html><body>Apartments:<br><pre>'.print_r($apartments).'</pre></body></html>');
            //return $this->redirectToRoute('task_success');
            //return new Response('<html><body>Apartments:<br><pre>'.$str.'</pre></body></html>');
            //$this->get('session')->getFlashBag()->add('form-message', '<hr>'.$str);
            return $this->render('feed.html.twig', [
                'form' => $form->createView(), 'do_result' => true,
                'count_all'=>$count_all, 'count_new'=>$count_new,'count_upd'=>$count_upd,'count_del'=>$count_del,
                'url'=>$enter_url
            ]);
        }

        return $this->render('feed.html.twig', [
            'form' => $form->createView(), 'do_result' => false
        ]);
    }
}

