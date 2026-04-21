<?php

namespace App\Tests\Entity;

use App\Entity\ForumComment;
use App\Entity\ForumPost;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ForumCommentTest extends TestCase
{
    public function testInitialState(): void
    {
        $comment = new ForumComment();
        $this->assertNotNull($comment->getCreatedAt());
    }

    public function testSetAndGetContent(): void
    {
        $comment = new ForumComment();
        $comment->setContent('Great post!');
        $this->assertEquals('Great post!', $comment->getContent());
    }

    public function testAssociations(): void
    {
        $comment = new ForumComment();
        $user = new User();
        $post = new ForumPost();

        $comment->setUser($user);
        $comment->setPost($post);

        $this->assertSame($user, $comment->getUser());
        $this->assertSame($post, $comment->getPost());
    }
}
