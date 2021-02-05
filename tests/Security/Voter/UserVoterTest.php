<?php


namespace App\Tests\Security\Voter;


use App\Entity\Task;
use App\Entity\User;
use App\Security\Voter\UserVoter;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

//Test private class is foolish. I just did for training.
class UserVoterTest extends WebTestCase
{
    private $tokenInterface;

    public function setUp(): void
    {
        $this->tokenInterface = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->getMock();
    }

    use FixturesTrait;

    // voteOnAttribute become public
    protected static function getMethod($name): ReflectionMethod
    {
        $class = new ReflectionClass('App\Security\Voter\UserVoter');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testVoteOnAttributeWrongAttribute()
    {
        $this->tokenInterface->expects($this->once())->method('getUser')->willReturn(new User());

        $voteOnAttribute = self::getMethod('voteOnAttribute');
        $userVoter = new UserVoter();
        $result = $voteOnAttribute->invoke($userVoter, 'TASK_WRONG', new Task(), $this->tokenInterface);

        $this->assertSame(false, $result);

    }
}