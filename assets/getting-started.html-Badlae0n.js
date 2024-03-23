import{_ as a,o as n,c as e,e as s}from"./app-6F16yZ-9.js";const t={},o=s(`<h1 id="getting-started" tabindex="-1"><a class="header-anchor" href="#getting-started"><span>Getting Started</span></a></h1><h2 id="installation" tabindex="-1"><a class="header-anchor" href="#installation"><span>Installation</span></a></h2><p>The bundle requires PHP 8.2 or higher, Symfony 7 or higher. Run the following command to install it in your application:</p><div class="language-bash" data-ext="sh" data-title="sh"><pre class="language-bash"><code><span class="token function">composer</span> require friendsofopentelemetry/opentelemetry-bundle
</code></pre></div><h3 id="versions" tabindex="-1"><a class="header-anchor" href="#versions"><span>Versions</span></a></h3><table><thead><tr><th>Version</th><th>Branch</th><th>PHP</th><th>Symfony</th></tr></thead><tbody><tr><td>dev</td><td><code>main</code></td><td><code>&gt;=8.2</code></td><td><code>^7.0</code></td></tr></tbody></table><h2 id="usage" tabindex="-1"><a class="header-anchor" href="#usage"><span>Usage</span></a></h2><p>This bundle is not yet available using Symfony Flex, so you have to manually configure it.</p><p>In your <code>config/bundles.php</code> file, add the following line at the end of the array:</p><div class="language-php" data-ext="php" data-title="php"><pre class="language-php"><code><span class="token keyword">return</span> <span class="token punctuation">[</span>
    <span class="token comment">// ...</span>
    <span class="token class-name class-name-fully-qualified static-context">FriendsOfOpenTelemetry<span class="token punctuation">\\</span>OpenTelemetryBundle</span><span class="token operator">::</span><span class="token keyword">class</span> <span class="token operator">=&gt;</span> <span class="token punctuation">[</span><span class="token string single-quoted-string">&#39;all&#39;</span> <span class="token operator">=&gt;</span> <span class="token constant boolean">true</span><span class="token punctuation">]</span><span class="token punctuation">,</span>
<span class="token punctuation">]</span><span class="token punctuation">;</span>
</code></pre></div><p>Then, create a new file <code>config/packages/open_telemetry.yaml</code> and add the following configuration:</p><div class="language-yaml" data-ext="yml" data-title="yml"><pre class="language-yaml"><code><span class="token key atrule">open_telemetry</span><span class="token punctuation">:</span>
  <span class="token key atrule">service</span><span class="token punctuation">:</span>
    <span class="token key atrule">namespace</span><span class="token punctuation">:</span> <span class="token string">&#39;MyCompany&#39;</span>
    <span class="token key atrule">name</span><span class="token punctuation">:</span> <span class="token string">&#39;MyApp&#39;</span>
    <span class="token key atrule">version</span><span class="token punctuation">:</span> <span class="token string">&#39;1.0.0&#39;</span>
    <span class="token key atrule">environment</span><span class="token punctuation">:</span> <span class="token string">&#39;%kernel.environment%&#39;</span>
</code></pre></div>`,12),l=[o];function p(i,c){return n(),e("div",null,l)}const d=a(t,[["render",p],["__file","getting-started.html.vue"]]),u=JSON.parse('{"path":"/user-guide/getting-started.html","title":"Getting Started","lang":"en-US","frontmatter":{},"headers":[{"level":2,"title":"Installation","slug":"installation","link":"#installation","children":[{"level":3,"title":"Versions","slug":"versions","link":"#versions","children":[]}]},{"level":2,"title":"Usage","slug":"usage","link":"#usage","children":[]}],"filePathRelative":"user-guide/getting-started.md","git":{"createdTime":1711212302000,"updatedTime":1711213180000,"contributors":[{"name":"Gaël Reyrol","email":"me@gaelreyrol.dev","commits":2}]}}');export{d as comp,u as data};
