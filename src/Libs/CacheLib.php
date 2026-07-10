<?php

namespace App\Libs;

/**
 * Wrapper sobre APCu com fallback gracioso e padrão remember().
 *
 * Uso básico:
 *   $cache = new CacheLib();
 *   $dados = $cache->remember('chave', 300, fn() => $dao->buscar());
 */
class CacheLib
{
    private string $prefix;
    private bool $available;

    public function __construct(string $prefix = 'speedphp')
    {
        $this->prefix    = $prefix;
        $this->available = \function_exists('apcu_fetch') && \apcu_enabled();
    }

    /**
     * Retorna valor do cache ou executa $callback, armazena e retorna o resultado.
     * É o padrão mais usado — elimina boilerplate nos controllers.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if (!$this->available) {
            return $callback();
        }

        $value = \apcu_fetch($this->buildKey($key), $found);
        if ($found) {
            return $value;
        }

        $value = $callback();
        \apcu_store($this->buildKey($key), $value, $ttl);

        return $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->available) {
            return $default;
        }

        $value = \apcu_fetch($this->buildKey($key), $found);

        return $found ? $value : $default;
    }

    public function set(string $key, mixed $value, int $ttl = 300): bool
    {
        if (!$this->available) {
            return false;
        }

        return \apcu_store($this->buildKey($key), $value, $ttl);
    }

    public function has(string $key): bool
    {
        if (!$this->available) {
            return false;
        }

        return \apcu_exists($this->buildKey($key));
    }

    public function delete(string $key): bool
    {
        if (!$this->available) {
            return false;
        }

        return \apcu_delete($this->buildKey($key));
    }

    /**
     * Invalida todas as chaves do namespace desta instância.
     * Útil para flush após operações de escrita.
     */
    public function flush(): void
    {
        if (!$this->available) {
            return;
        }

        $info = \apcu_cache_info(false);
        foreach ($info['cache_list'] ?? [] as $entry) {
            $entryKey = $entry['info'] ?? '';
            if (\str_starts_with($entryKey, $this->prefix . ':')) {
                \apcu_delete($entryKey);
            }
        }
    }

    private function buildKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }
}
