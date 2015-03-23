<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class AbstractExportWriterTest extends WebTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MagentoTransportInterface
     */
    protected $transport;

    protected function setUp()
    {
        parent::setUp();

        $this->initClient();

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);

        $this->transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
    }

    protected function tearDown()
    {
        // clear DB from separate connection
        $batchJobManager = $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobInstance')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:JobExecution')->execute();
        $batchJobManager->createQuery('DELETE AkeneoBatchBundle:StepExecution')->execute();

        unset($this->transport);
    }

    /**
     * @param string $alias
     * @param string $status
     *
     * @return JobExecution[]
     */
    protected function getJobs($alias, $status)
    {
        $qb = $this->getContainer()->get('oro_entity.doctrine_helper')
            ->getEntityRepository('AkeneoBatchBundle:JobInstance')
            ->createQueryBuilder('job');

        $qb
            ->select('job')
            ->leftJoin('job.jobExecutions', 'jobExecutions')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('job.alias', ':alias'),
                    $qb->expr()->eq('jobExecutions.status', ':status')
                )
            )
            ->setParameter('alias', $alias)
            ->setParameter('status', $status);

        return $qb->getQuery()->getResult();
    }
}
