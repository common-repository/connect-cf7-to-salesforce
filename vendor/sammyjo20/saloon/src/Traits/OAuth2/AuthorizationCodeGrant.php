<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use DateTimeImmutable;
use Saloon\Helpers\Str;
use Saloon\Helpers\Date;
use InvalidArgumentException;
use Saloon\Contracts\Request;
use Saloon\Helpers\URLHelper;
use Saloon\Contracts\Response;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\OAuth2\GetUserRequest;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Exceptions\InvalidStateException;
use Saloon\Http\OAuth2\GetAccessTokenRequest;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\OAuth2\GetRefreshTokenRequest;

trait AuthorizationCodeGrant
{
    use HasOAuthConfig;

    /**
     * The state generated by the getAuthorizationUrl method.
     *
     * @var string|null
     */
    protected ?string $state = null;

    /**
     * Get the Authorization URL.
     *
     * @param array<string> $scopes
     * @param string|null $state
     * @param string $scopeSeparator
     * @param array $additionalQueryParameters
     * @return string
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     */
    public function getAuthorizationUrl(array $scopes = [], string $state = null, string $scopeSeparator = ' ', array $additionalQueryParameters = []): string
    {
        $config = $this->oauthConfig();

        $config->validate();

        $clientId = $config->getClientId();
        $redirectUri = $config->getRedirectUri();
        $defaultScopes = $config->getDefaultScopes();

        $this->state = $state ?? Str::random(32);

        $queryParameters = [
            'response_type' => 'code',
            'scope' => implode($scopeSeparator, array_merge($defaultScopes, $scopes)),
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $this->state,
            ...$additionalQueryParameters,
        ];

        $query = http_build_query($queryParameters, '', '&', PHP_QUERY_RFC3986);
        $query = trim($query, '?&');

        $url = URLHelper::join($this->resolveBaseUrl(), $config->getAuthorizeEndpoint());

        $glue = str_contains($url, '?') ? '&' : '?';

        return $url . $glue . $query;
    }

    /**
     * Get the access token.
     *
     * @template TRequest of \Saloon\Contracts\Request
     *
     * @param string $code
     * @param string|null $state
     * @param string|null $expectedState
     * @param bool $returnResponse
     * @param callable(TRequest): (void)|null $requestModifier
     * @return \Saloon\Contracts\OAuthAuthenticator|\Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\InvalidStateException
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function getAccessToken(string $code, string $state = null, string $expectedState = null, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate();

        if (! empty($state) && ! empty($expectedState) && $state !== $expectedState) {
            throw new InvalidStateException;
        }

        $request = $this->resolveAccessTokenRequest($code, $this->oauthConfig());

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);


        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response);
    }

    /**
     * Refresh the access token.
     *
     * @template TRequest of \Saloon\Contracts\Request
     *
     * @param \Saloon\Contracts\OAuthAuthenticator|string $refreshToken
     * @param bool $returnResponse
     * @param callable(TRequest): (void)|null $requestModifier
     * @return \Saloon\Contracts\OAuthAuthenticator|\Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\OAuthConfigValidationException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function refreshAccessToken(OAuthAuthenticator|string $refreshToken, bool $returnResponse = false, ?callable $requestModifier = null): OAuthAuthenticator|Response
    {
        $this->oauthConfig()->validate();

        if ($refreshToken instanceof OAuthAuthenticator) {
            if ($refreshToken->isNotRefreshable()) {
                throw new InvalidArgumentException('The provided OAuthAuthenticator does not contain a refresh token.');
            }

            $refreshToken = $refreshToken->getRefreshToken();
        }

        $request = $this->resolveRefreshTokenRequest($this->oauthConfig(), $refreshToken);

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throw();

        return $this->createOAuthAuthenticatorFromResponse($response, $refreshToken);
    }

    /**
     * Create the OAuthAuthenticator from a response.
     *
     * @param \Saloon\Contracts\Response $response
     * @param string|null $fallbackRefreshToken
     * @return \Saloon\Contracts\OAuthAuthenticator
     */
    protected function createOAuthAuthenticatorFromResponse(Response $response, string $fallbackRefreshToken = null): OAuthAuthenticator
    {
        $responseData = $response->object();

        $accessToken = $responseData->access_token;
        $refreshToken = $responseData->refresh_token ?? $fallbackRefreshToken;
        $expiresAt = isset($responseData->expires_in) && is_numeric($responseData->expires_in)
            ? Date::now()->addSeconds((int) $responseData->expires_in)->toDateTime()
            : null;

        return $this->createOAuthAuthenticator($accessToken, $refreshToken, $expiresAt);
    }

    /**
     * Create the authenticator.
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param DateTimeImmutable|null $expiresAt
     * @return OAuthAuthenticator
     */
    protected function createOAuthAuthenticator(string $accessToken, string $refreshToken, ?DateTimeImmutable $expiresAt = null): OAuthAuthenticator
    {
        return new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);
    }

    /**
     * Get the authenticated user.
     *
     * @template TRequest of \Saloon\Contracts\Request
     *
     * @param \Saloon\Contracts\OAuthAuthenticator $oauthAuthenticator
     * @param callable(TRequest): (void)|null $requestModifier
     * @return \Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function getUser(OAuthAuthenticator $oauthAuthenticator, ?callable $requestModifier = null): Response
    {
        $request = $this->resolveUserRequest($this->oauthConfig())->authenticate($oauthAuthenticator);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        return $this->send($request);
    }

    /**
     * Get the state that was generated in the getAuthorizationUrl() method.
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Resolve the access token request
     *
     * @param string $code
     * @param OAuthConfig $oauthConfig
     * @return Request
     */
    protected function resolveAccessTokenRequest(string $code, OAuthConfig $oauthConfig): Request
    {
        return new GetAccessTokenRequest($code, $oauthConfig);
    }

    /**
     * Resolve the refresh token request
     *
     * @param OAuthConfig $oauthConfig
     * @param string $refreshToken
     * @return Request
     */
    protected function resolveRefreshTokenRequest(OAuthConfig $oauthConfig, string $refreshToken): Request
    {
        return new GetRefreshTokenRequest($oauthConfig, $refreshToken);
    }

    /**
     * Resolve the user request
     *
     * @param OAuthConfig $oauthConfig
     * @return Request
     */
    protected function resolveUserRequest(OAuthConfig $oauthConfig): Request
    {
        return new GetUserRequest($oauthConfig);
    }
}
