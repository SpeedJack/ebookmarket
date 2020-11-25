<?php
namespace EbookMarket\Entity;

/**
 * @brief Represents a user.
 *
 * @author Niccolò Scatena <speedjack95@gmail.com>
 * @copyright GNU General Public License, version 3
 */
class Category extends AbstractEntity {
    private $name;

    public function __construct(int $id, string $name)
    {
        parent::__construct($id);
        $this->name = $name;
    }
}