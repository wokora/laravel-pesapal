<?php

namespace wokora\pesapal\Contracts;



interface PesapalContract
{
    /**
     * Pending payment
     */
    const PESAPAL_STATUS_PENDING = 'pending';

    /**
     * Failed payment
     */
    const PESAPAL_STATUS_FAILED = 'failed';

    /**
     * Successfully completed payment
     */
    const PESAPAL_STATUS_COMPLETED = 'completed';
}
