<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Security\Authorization\Voter;

use Chamilo\CoreBundle\Entity\Course;
use Chamilo\CoreBundle\Entity\Manager\CourseManager;
use Chamilo\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CourseVoter
 * @package Chamilo\CoreBundle\Security\Authorization\Voter
 */
class CourseVoter extends AbstractVoter
{
    const VIEW = 'VIEW';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';

    private $entityManager;
    private $courseManager;

    /**
     * @param EntityManager $entityManager
     * @param CourseManager $courseManager
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManager $entityManager,
        CourseManager $courseManager,
        ContainerInterface $container
    ) {
        $this->entityManager = $entityManager;
        $this->courseManager = $courseManager;
        $this->container = $container;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return CourseManager
     */
    public function getCourseManager()
    {
        return $this->courseManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return array(self::VIEW, self::EDIT, self::DELETE);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return array('Chamilo\CoreBundle\Entity\Course');
    }

    /**
     * Check if a user has permissions in a course
     *
     * @param string $attribute
     * @param Course $course
     * @param User $user
     * @return bool
     */
    protected function isGranted($attribute, $course, $user = null)
    {
        // Make sure there is a user object (i.e. that the user is logged in)
        // Anons can enter a course depending of the course visibility
        /*if (!$user instanceof UserInterface) {
            return false;
        }*/

        $authChecker = $this->container->get('security.authorization_checker');

        // Admins have access to everything
        if ($authChecker->isGranted('ROLE_ADMIN')) {

            return true;
        }

        // Course is active?
        if (!$course->isActive()) {

            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                // "Open to the world" no need to check if user is registered
                if ($course->isPublic()) {

                    return true;
                }

                // Other course visibility need to have a user set
                if (!$user instanceof UserInterface) {
                    return false;
                }

                // User is subscribed in the course no matter if is teacher/student
                if ($course->hasUser($user)) {

                    $user->addRole(ResourceNodeVoter::ROLE_CURRENT_COURSE_STUDENT);

                    return true;
                }

                break;
            case self::EDIT:
            case self::DELETE:
                // Only teacher can edit/delete stuff
                if ($course->hasTeacher($user)) {
                    $user->addRole(ResourceNodeVoter::ROLE_CURRENT_COURSE_TEACHER);

                    return true;
                }
                break;
        }

        return false;
    }
}
