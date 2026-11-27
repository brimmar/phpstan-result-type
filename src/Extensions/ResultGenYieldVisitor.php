<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Extensions;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every yield that belongs to the closure given to Result::gen().
 *
 * ResultGenYieldTypeExtension reads the mark. A yield in a nested closure
 * belongs to that closure's own generator and stays unmarked.
 */
class ResultGenYieldVisitor extends NodeVisitorAbstract
{
    public const string ATTRIBUTE = 'brimmarResultGenYield';

    /** @var array<int, true> */
    private array $genClosures = [];

    /** @var list<bool> */
    private array $enclosingIsGen = [];

    /**
     * @param class-string $resultInterface
     */
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Result') {}

    #[\Override]
    public function beforeTraverse(array $nodes): null
    {
        $this->genClosures = [];
        $this->enclosingIsGen = [];

        return null;
    }

    #[\Override]
    public function enterNode(Node $node): null
    {
        if ($node instanceof StaticCall) {
            $callback = $this->genCallback($node);
            if ($callback !== null) {
                $this->genClosures[spl_object_id($callback)] = true;
            }
        } elseif ($node instanceof FunctionLike) {
            $this->enclosingIsGen[] = isset($this->genClosures[spl_object_id($node)]);
        } elseif ($node instanceof Yield_ && array_last($this->enclosingIsGen) === true) {
            $node->setAttribute(self::ATTRIBUTE, true);
        }

        return null;
    }

    #[\Override]
    public function leaveNode(Node $node): null
    {
        if ($node instanceof FunctionLike) {
            array_pop($this->enclosingIsGen);
        }

        return null;
    }

    private function genCallback(StaticCall $call): ?Closure
    {
        if (! $call->name instanceof Identifier
            || $call->name->toString() !== 'gen'
            || ! $call->class instanceof Name
            || $call->class->toString() !== $this->resultInterface
            || $call->isFirstClassCallable()) {
            return null;
        }

        $callback = ($call->getArgs()[0] ?? null)?->value;

        return $callback instanceof Closure ? $callback : null;
    }
}
