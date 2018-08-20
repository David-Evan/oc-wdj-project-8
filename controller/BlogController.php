<?php

namespace Controller;

use Library\BaseController;
use Model\Manager\{BlogPostsManager, CommentsManager};
use Model\Entity\{BlogPost, Comment};

class BlogController extends BaseController{

    const ENVIRONNEMENT = 'frontend';

    /**
     * {blog?} {id}
     * @return string - index view (list of last Posts)
     */
    public function indexAction(){
        
        $blogPostManager = new BlogPostsManager;

        return $this->twig->render(self::ENVIRONNEMENT.'/index.html', array(

            'BlogPosts' => $blogPostManager->getAllPublishedPosts(),
        ));
    }

    /**
     * {blog?} + {id}
     * @return string - Single Post view
     */
    public function viewBlogPostAction($id){

        $blogPostManager = new BlogPostsManager;

        $blogPost = $blogPostManager->getPostByID($id);

        if(!$blogPost or $blogPost->status !== 'published')
            return $this->redirect404();


        $commentManager = new CommentsManager;
        $postComments = $commentManager->getAllCommentsForPostID($id);


        return $this->twig->render(self::ENVIRONNEMENT.'/viewBlogPost.html', array(
            'BlogPost' => $blogPost,
            'Comments' => $postComments,
        ));

    }

    /**
     * Ajax POST : {blog?} + $_POST 
     */
    public function publishCommentAction($post){

        $comment = new Comment;

        $comment->setPostID($post['postID']);
        $comment->setAuthorEmail($post['authorEmail']);
        $comment->setAuthorName($post['authorName']);
        $comment->setContent($post['commentContent']);
        $comment->setStatus(Comment::DEFAULT_STATUS);
        
        $commentManager = new CommentsManager;

        if($commentManager->createComment($comment))
            return self::AJAX_SUCCESS_RETURN;
        else
            return self::AJAX_FAIL_RETURN;
    }
}