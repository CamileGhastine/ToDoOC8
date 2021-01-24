<?php


namespace App\Tests\Service;


use App\Entity\User;
use App\Service\UserFormHandler;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class UserFormHandlerTest extends KernelTestCase
{
    use FixturesTrait;

    private $form;
    private $em;
    private $passwordEncoder;
    private $flash;
    private $request;

    public function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(['handleRequest', 'isSubmitted', 'isValid'])
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();

        $this->passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface')
            ->getMock();

        $this->flash = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
            ->getMock();

        $this->request = new Request();
    }

    public function testHandleFormReturnFalseWhenFormIsNotSubmitted()
    {
        $result = $this->handle(new User(), false, false);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnFalseWhenFormIsSubmittedAndNotValid()
    {
        $result = $this->handle(new User(), true, false);

        $this->assertSame(false, $result);
    }

    public function testHandleFormReturnTrueWhenCreateFormIsSubmittedAndValid()
    {
        $user = (new User())->setPassword('password');
        $result = $this->handle($user, true, true);

        $this->assertNotSame('password', $user->getPassword());
        $this->assertSame(true, $result);
    }

    public function testHandleFormReturnTrueWhenEditFormIsSubmittedAndValid()
    {
        $user = $this->getMockBuilder('App\Entity\User')->setMethods(['getId'])->getMock();
        $user->method('getId')->willReturn(1);

        $result = $this->handle($user, true, true);

        $this->assertSame(true, $result);
    }


    private function handle(User $user, ?bool $submitted = null, ?bool $valid = null): bool
    {
        $this->setFormMethods($user, $submitted, $valid);

        $handleForm = new UserFormHandler($this->em, $this->passwordEncoder, $this->flash);
        return $handleForm->handle($this->request, $this->form, $user);
    }

    private function setFormMethods(User $user, ?bool $submitted = null, ?bool $valid = null)
    {
        $submittedTrue = $submitted ? 'once' : 'never';
        $submittedValidTrue = $submitted && $valid ? 'once' : 'never';
        $submittedValidCreateForm = $submitted && $valid && !$user->getId() ? 'once' : 'never';
        $flashMessage = $submitted && $valid && !$user->getId() ? 'ajouté' : 'modifié';

        $this->em->expects($this->$submittedValidCreateForm())->method('persist');
        $this->em->expects($this->$submittedValidTrue())->method('flush');

        $this->passwordEncoder->expects($this->$submittedValidCreateForm())->method('encodePassword');

        $this->flash->expects($this->$submittedValidTrue())
            ->method('add')
            ->with($this->equalTo('success'), $this->stringContains($flashMessage));

        $this->form
            ->expects($this->once())
            ->method('handleRequest')
            ->willReturn($this->form);
        $this->form
            ->expects($this->once())
            ->method('isSubmitted')
            ->willReturn($submitted);
        $this->form
            ->expects($this->$submittedTrue())
            ->method('isValid')
            ->willReturn($valid);
    }
}
