<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBandle\Entity\Users;
use AppBandle\Entity\Categories;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class Admin extends Controller
{
    /**
     * @Route("/", name="login_page")
     */
    public function indexAction(Request $request)
    {
        return $this->render('admin/index.html.twig');
    }

    /**                                 
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $username = $request->get('email');
        $password = $request->get('password');
        $output   = array();

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:Users')
            ->findOneBy(
                array('username' => $username, 'active' => 'yes')
            );

        if (!$user)
        {
            $output['success'] = 0;
            $output['message'] = $this->get('translator')->trans('message.user_not_found');
        }
        else
        {
            if (md5($password) == $user->getPassword())
            {
                $this->get('session')->set('userId', $user->getId());

                $output['success'] = 1;
                $output['url']     = $request->getBaseURL().'/admin';
            }
            else
            {
                $output['success'] = 0;
                $output['message'] = $this->get('translator')->trans('message.invalid_login_data');
            }
        }

        return new Response(json_encode($output));
    }

    /**
     * @Route("/admin", name="dashboard")
     */
    public function adminAction(Request $request)
    {
        return $this->render('admin/dashboard.html.twig');
    }

    /**
     * @Route("/admin/category/", name="admin_categories")
     */
    public function categoryAction(Request $request)
    {
        return $this->render('admin/category.html.twig');
    }

    /**
     * @Route("/admin/category/new/", name="admin_new_categy")
     */
    public function newCategoryAction(Request $request)
    {
        return $this->render('admin/new_category.html.twig');
    }

    /**
     * @Route("/admin/saveCategory", name="save_category")
     */
    public function saveCategoryAction(Request $request)
    {
        $fileURL = __DIR__.'/../../../web/assets/upload';

        $formData   = $request->get('form_data');
        $parsedData = array();
        parse_str($formData, $parsedData);

        $category_name = $parsedData['category_name'];
        $description   = $parsedData['description'];
        $now           = new \DateTime("now");
        $output        = array();
        
        $picture       = $request->files->get('file');

        $category = new \AppBundle\Entity\Categories();

        if ($picture)
        {
            $filename      = $picture->getClientOriginalName();
            $parts         = explode('.', $filename);
            $extension     = end($parts);
            $filename      = str_replace('.'.$extension, '_'.uniqid().'.'.$extension, $filename);
            $filesize      = $picture->getSize();
            $error         = $picture->getError();
            $pathName      = $picture->getPathName();

            if (!is_dir($fileURL.'/categories/'))
            {
                mkdir($fileURL.'/categories/', 0755, true);
            }

            if (move_uploaded_file($pathName, $fileURL.'/categories/'.$filename))
            {
                $category->setImage($filename);
            }
        }
        else
        {
            $filename = "";
            $category->setImage($filename);
        }
            
        $category->setCategoryName($category_name);
        $category->setDescription($description);
        $category->setCreatedDate($now);
        $category->setActive('yes');

        $em = $this->getDoctrine()->getManager();    
        $em->persist($category);
        $em->flush();

        if ($category)
        {
            $output['success'] = 1;
            $output['message'] = $this->get('translator')->trans('message.category_add_success');
        }
        else
        {
            $output['success'] = 0;
            $output['message'] = $this->get('translator')->trans('message.category_add_error');
        }

        return new Response(json_encode($output));
    }

    /**
     * @Route("/categoriesTable", name="categories_table")
     */
    public function categoriesTableAction(Request $request)
    {
        $start  = $request->query->get('iDisplayStart');
        $length = $request->query->get('iDisplayLength');
        $limit  = $length - $start;
        $search = $request->query->get('sSearch');

        $em    = $this->getDoctrine()->getManager(); 
        $query = $em->createQuery('SELECT c FROM AppBundle:Categories c')->setFirstResult($start)->setMaxResults($limit);
        $categories = $query->getResult(); 
        $categories_pagination = $this->getDoctrine()->getRepository('AppBundle:Categories')->findAll();

        $categories_data = array();

        if (is_array($categories) && count($categories)  > 0)
        {
            for ($j = 0; $j < count($categories); $j++) 
            {
                $categories_data[$j][] = $categories[$j]->getId();
                $categories_data[$j][] = $categories[$j]->getCategoryName();
                $categories_data[$j][] = $categories[$j]->getDescription();
                $categories_data[$j][] = $categories[$j]->getImage();
                $categories_data[$j][] = $categories[$j]->getActive();
                $categories_data[$j][] = '
                    <a href="'.$request->getBaseURL().'/admin/category/edit/'.$categories[$j]->getId().'"><button class="btn btn-success">'.$this->get('translator')->trans('label.edit').'</button></a>
                    <a href="'.$request->getBaseURL().'/admin/category/delete/'.$categories[$j]->getId().'" onclick="deleteCategory(event, '.$categories[$j]->getId().');"><button class="btn btn-danger">'.$this->get('translator')->trans('label.delete_file').'</button></a>
                ';
            }   
        }

        $output = array(
            "sEcho" => intval($request->query->get('sEcho')),
            "iTotalRecords" => count($categories_pagination),
            "iTotalDisplayRecords" => count($categories_pagination),
            "aaData" => $categories_data
        );

        return new Response(json_encode($output));
    }

    /**
     * @Route("/admin/category/edit/{id}", name="edit_categories")
     */
    public function editCategoryAction($id)
    {
        $category = $this->getDoctrine()->getRepository('AppBundle:Categories')->findOneById($id);

        return $this->render('admin/edit_category.html.twig', 
            array('category' => $category)
        );
    }

    /**
     * @Route("/admin/editCategory", name="edit_category")
     */
    public function saveEditCategoryAction(Request $request)
    {
        $fileURL = __DIR__.'/../../../web/assets/upload';

        $output     = array();
        $formData   = $request->get('form_data');
        $parsedData = array();
        parse_str($formData, $parsedData);

        $category_name = $parsedData['category_name'];
        $description   = $parsedData['description'];
        $category_id   = $request->get('category_id');

        $category = $this->getDoctrine()->getRepository('AppBundle:Categories')->findOneById($category_id);

        if (!$category)
        {
            $output['success'] = 0;
            $output['message'] = $this->get('translator')->trans('message.category_add_error');
        }
        else
        {
            $picture = $request->files->get('file');

            if ($picture)
            {
                $filename      = $picture->getClientOriginalName();
                $parts         = explode('.', $filename);
                $extension     = end($parts);
                $filename      = str_replace('.'.$extension, '_'.uniqid().'.'.$extension, $filename);
                $filesize      = $picture->getSize();
                $error         = $picture->getError();
                $pathName      = $picture->getPathName();

                if (!is_dir($fileURL.'/categories/'))
                {
                    mkdir($fileURL.'/categories/', 0755, true);
                }

                if (!file_exists($fileURL.'/categories/'.$filename))
                {
                    move_uploaded_file($pathName, $fileURL.'/categories/'.$filename);
                }

                $category->setImage($filename);
            }

            $category->setCategoryName($category_name);
            $category->setDescription($description);

            $em = $this->getDoctrine()->getManager();

            $em->persist($category);
            $em->flush();

            $output['success'] = 1;
            $output['message'] = $this->get('translator')->trans('message.category_edit_success');
        }

        return new Response(json_encode($output));
    }

    /**
     * @Route("/admin/deleteCategory", name="delete_category")
     */
    public function deleteCategoryAction(Request $request)
    {
        $fileURL = __DIR__.'/../../../web/assets/upload';

        $category_id = $request->get('category_id');

        $output   = array();
        $category = $this->getDoctrine()->getRepository("AppBundle:Categories")->findOneById($category_id);

        if (!$category)
        {
            $output['success'] = 0;
            $output['message'] = $this->get('translator')->trans('message.delete_category_error');
        }
        else
        {
            $image = $category->getImage();

            if (strlen($image) > 0)
            {
                if (file_exists($fileURL.'/categories/'.$image))
                {
                    unlink($fileURL.'/categories/'.$image);
                }
            }

            $em = $this->getDoctrine()->getManager();
            
            $em->remove($category);
            $em->flush();

            $output['success'] = 1;
            $output['message'] = $this->get('translator')->trans('message.delete_category_success');
        }

        return new Response(json_encode($output));
    }

    /**
     * @Route("/admin/deleteCategoryImage", name="delete_category_image")
     */
    public function deleteCategoryImageAction(Request $request)
    {
        $fileURL = __DIR__.'/../../../web/assets/upload';

        $category_id = $request->get('category_id');

        $category = $this->getDoctrine()->getRepository("AppBundle:Categories")->findOneById($category_id);

        if (!$category)
        {
            $output['success'] = 0;
            $output['message'] = $this->get('translator')->trans('message.error_delete_category_image');
        }
        else
        {
            $image = $category->getImage();

            if (file_exists($fileURL.'/categories/'.$image))
            {
                unlink($fileURL.'/categories/'.$image);
            }

            $em = $this->getDoctrine()->getManager();

            $filename = "";
            $category->setImage($filename);

            $em->persist($category);
            $em->flush();

            $output['success'] = 1;
        }

        return new Response(json_encode($output));
    }
}
