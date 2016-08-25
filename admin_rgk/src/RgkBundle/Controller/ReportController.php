<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 09:59
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\Section;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use RgkBundle\Entity\Rival;
use RgkBundle\Entity\Product;
use RgkBundle\Entity\Price;

class ReportController extends BaseController
{
    /**
     * @Route("/report")
     */
    public function indexAction(Request $request)
    {
        $params = $this->getTemplatteParams();
        //var_dump($request->get('_route'));
       // exit();
        return $this->render('RgkBundle:Admin:base.html.twig',$params);
    }

    public function getXLSColName($a){
        $a = intval($a);
        if($a==0)
            return chr(90);

        if($a<=26)
            return chr($a+64);
        else {
            $k = $a%26;
            return $this->getXLSColName(floor($a/26)-($k?0:1)).$this->getXLSColName($k);
        }
    }

    /**
     * @Route("/xls/{id}")
     */
    public function xlsAction($id=0){
        /**
         * @var PHPExcel $phpExcelObject
         */
        // section
        $params['sections'] = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array(), array('title' => 'ASC'));

        $active_section = false;
        if(!empty($params['sections'])){
            /**
             * @var Section $a
             */
            foreach ($params['sections'] as &$a){
                if($a->getId() == $id) {
                    $active_section = $a;
                    break;
                }
            }
        }
        if($active_section == false || $active_section->getFolder())
            $this->renderApiJson(['error'=>'Ошибка передачи раздела']);

        $params['sections'] = $this->menuStrict($params['sections']);
        $sectionSpectre = $this->getSectionChildTreeIds($active_section->getId(),$params['sections']);
        if(empty($sectionSpectre))
            $this->renderApiJson(['error'=>'Ошибка передачи раздела']);

        //get products of active section
        $prod = $this->getDoctrine()
            ->getRepository('RgkBundle:Product')
            ->findBy(array('section'=>$sectionSpectre), array('pos' => 'ASC','title' => 'ASC','price'=>'ASC'));

        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("Artoa")
            ->setTitle(sprintf('Отчет "%s"',$active_section->getTitle()));
        $phpExcelObject->setActiveSheetIndex(0);

        ///
        if(!empty($prod)) {

            //get all rivals array
            $rivals = $this->getSectionsRival($sectionSpectre,$active_section);

            $colorArray = array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '469dc1')
                )
            );

            //set header
            $phpExcelObject->setActiveSheetIndex(0)
                           ->setCellValue('A1', 'Товар')
                           ->mergeCells('B1:C1')
                           ->setCellValue('B1', 'Цена')
                           ->getStyle('A1:C1')
                           ->applyFromArray($colorArray);
            $startCol = 4;
            if(!empty($rivals)){
                $colorArray['fill']['color']['rgb'] = '88a5b1';
                $phpExcelObject->setActiveSheetIndex(0)
                               ->getStyle($this->getXLSColName($startCol).'1:'.$this->getXLSColName($startCol+(count($rivals)*2)-1).'1')
                               ->applyFromArray($colorArray);
                /**
                 * @var Rival $rival
                 */
                foreach ($rivals as $rival){
                    $phpExcelObject->setActiveSheetIndex(0)
                                   ->mergeCells($this->getXLSColName($startCol).'1:'.$this->getXLSColName($startCol+1).'1')
                                   ->setCellValue($this->getXLSColName($startCol).'1',$rival->getName());

                    $startCol += 2;
                }

            }

            /**
             * @var Product $item
             */
            $row = 1;
            foreach ($prod as $item){
                $row++;
                $colorArray['fill']['color']['rgb'] = str_replace('#','',$item->getLabel());
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A'.$row, $item->getTitle())
                    ->setCellValue('B'.$row, $item->getPrice())
                    ->setCellValue('C'.$row, $item->getPercent().'%')
                    ->getStyle('C'.$row)
                    ->applyFromArray($colorArray);

                if(!empty($rivals)){
                    /**
                     * @var Rival $rival
                     * @var Price $a
                     */
                    $startCol = 4;
                    foreach ($rivals as $rival){
                        $a = $item->getPrices()
                                  ->filter( function($a) use ($rival) {
                                                return ($a->getCode()->getRival()->getId() == $rival->getId());
                                          })
                                  ->first();
                        if($a && $a->getPrice()){
                            $colorArray['fill']['color']['rgb'] = str_replace('#','',$a->getLabel());
                            $phpExcelObject->setActiveSheetIndex(0)
                                ->setCellValue($this->getXLSColName($startCol).$row, $a->getPrice())
                                ->setCellValue($this->getXLSColName($startCol+1).$row, $a->getPercent().'%')
                                ->getStyle($this->getXLSColName($startCol+1).$row)
                                ->applyFromArray($colorArray);
                        }

                        $startCol += 2;
                    }
                }
            }
        }
        ///

        $phpExcelObject->getActiveSheet()->setTitle(sprintf('Отчет "%s"',$active_section->getTitle()));
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('report_%d.xls',$active_section->getId())
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}