<?php

namespace Controller;

use Library\{BaseController};
use Library\Paginator\ {Paginator, TablePaginatorOptions};
use Model\Manager\{BlogPostsManager, CommentsManager};
use Model\Entity\{BlogPost, Comment};
use Cocur\Slugify\Slugify;

class AdminController extends BaseController{

    const CONTROLLER_NAME = 'admin';

    /**
     * GET : {?p}
     * Show index page
     */
    public function indexAction($page = 1){

        $commentManager = new CommentsManager;

        $commentsInLastWeek = $commentManager->getAllCommentsInLastWeek();

        $commentsUnderSurvey = $commentManager->getAllCommentsUnderSurvey();

        if(!empty($commentsUnderSurvey)){

            $paginator = new Paginator($commentsUnderSurvey, new TablePaginatorOptions($page));
            $paginator->setURLTemplate('?p={page}');


            return $this->twig->render(self::CONTROLLER_NAME.'/index.html', array(
                'CommentsUnderSurvey' => $paginator->getElementsForCurrentPage(),
                'Paginator' => $paginator,
                'CommentsInLastWeeks' => $commentsInLastWeek,
            ));
        }
        else
            return $this->twig->render(self::CONTROLLER_NAME.'/index.html', array(
                'CommentsInLastWeeks' => $commentsInLastWeek
            ));
    }

    /**
     * GET : {?p}
     * Show All Blog Post
     */
    public function viewAllBlogPostsAction($page = 1){

        $blogPostsManager = new BlogPostsManager;

        $blogPosts = $blogPostsManager->getAllPosts();

        $paginator = new Paginator($blogPosts, new TablePaginatorOptions($page));
        $paginator->setURLTemplate('?a=viewAllBlogPost&p={page}');

        return $this->twig->render(self::CONTROLLER_NAME.'/viewAllBlogPosts.html', array(
            'BlogPosts' => $paginator->getElementsForCurrentPage(),
            'Paginator' => $paginator,
        ));
    }

    /**
     * Delete comment Action
     */
    public function deleteCommentAction($id){
        
        $commentManager = new CommentsManager;

        if(!is_numeric($id))
            return $this->redirect404();
        
        if($commentManager->deleteCommentByID($id))
            return $this->indexAction();
        else
            return $this->redirect404();
    }

    /**
     * Remove Survey on Comment Action
     */
    public function removeSurveyOnCommentAction($id){
        
        $commentManager = new CommentsManager;

        if(!is_numeric($id))
            return $this->redirect404();
        
        if($commentManager->removeSurveyOnComment($id))
            return $this->indexAction();
        else
            return $this->redirect404();
    }


    /**
     * Delete Blog Post
     */
    public function deleteBlogPostAction($id){
        
        $blogPostsManager = new BlogPostsManager;

        if(!is_numeric($id))
            return $this->redirect404();
        
        if($blogPostsManager->deletePostByID($id))
            return $this->viewAllBlogPostsAction();
        else
            return $this->redirect404();
    }


    public function changeBlogPostStatusAction($id){

        $blogPostsManager = new BlogPostsManager;

        if($blogPostsManager->changePostStatus($id));
            return $this->viewAllBlogPostsAction();
        
        return $this->redirect404();

    }

    public function createBlogPostAction($post){
        
        $sluger = new Slugify; 
        $blogPost = new BlogPost;

        $blogPost->setStatus($_POST['status']);
        $blogPost->setTitle($_POST['title']);
        $blogPost->setContent($_POST['content']);
        $blogPost->setAuthorName(BlogPost::AUTHOR_NAME);
        $blogPost->setSlug($sluger->slugify($_POST['title']));

        $blogPostsManager = new BlogPostsManager;

        if($blogPostsManager->createPost($blogPost))
            return $this->viewAllBlogPostsAction();
    }

    public function updateBlogPostAction(){
        $blogPost = new BlogPost;

        $blogPost->setStatus($_POST['status']);
        $blogPost->setTitle($_POST['title']);
        $blogPost->setContent($_POST['content']);
        $blogPost->setId($_POST['postID']);

        $blogPostsManager = new BlogPostsManager;

        if($blogPostsManager->updatePost($blogPost))
            return $this->viewAllBlogPostsAction();

        else
            return $this->redirect404();
    }

    public function showCreateBlogPostFormAction(){
        return $this->twig->render(self::CONTROLLER_NAME.'/createBlogPost.html');
    }

    public function showUpdateBlogPostFormAction($id){
        $blogPostsManager = new BlogPostsManager;

        if(empty($blogPost = $blogPostsManager->getPostByID($id)))
            return $this->redirect404();

        return $this->twig->render(self::CONTROLLER_NAME.'/updateBlogPost.html', array(
            'BlogPost' => $blogPost,
        ));
    }
}