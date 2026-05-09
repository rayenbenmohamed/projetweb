<?php

namespace App\Tests\Entity;

use App\Entity\ForumPost;
use App\Entity\ForumLike;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ForumPostTest extends TestCase
{
    public function testInitialState(): void
    {
        $post = new ForumPost();
        
        $this->assertNotNull($post->getCreatedAt());
        $this->assertNotNull($post->getUpdatedAt());
        $this->assertEquals('PUBLISHED', $post->getStatus());
        $this->assertTrue($post->isActive());
        $this->assertCount(0, $post->getComments());
        $this->assertCount(0, $post->getLikes());
    }

    public function testSetAndGetTitle(): void
    {
        $post = new ForumPost();
        $post->setTitle('Test Title');
        
        $this->assertEquals('Test Title', $post->getTitle());
    }

    public function testAddAndRemoveLike(): void
    {
        $post = new ForumPost();
        $like = new ForumLike();
        
        $post->addLike($like);
        $this->assertCount(1, $post->getLikes());
        $this->assertSame($post, $like->getPost());
        
        $post->removeLike($like);
        $this->assertCount(0, $post->getLikes());
        $this->assertNull($like->getPost());
    }

    public function testIsLikedBy(): void
    {
        $post = new ForumPost();
        
        // Mock user with ID
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        
        $like = new ForumLike();
        $like->setUser($user);
        
        $this->assertFalse($post->isLikedBy($user));
        
        $post->addLike($like);
        $this->assertTrue($post->isLikedBy($user));
        
        // Different user
        $otherUser = $this->createMock(User::class);
        $otherUser->method('getId')->willReturn(2);
        $this->assertFalse($post->isLikedBy($otherUser));
    }
}
