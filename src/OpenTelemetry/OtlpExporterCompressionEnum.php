<?php

namespace GaelReyrol\OpenTelemetryBundle\OpenTelemetry;

enum OtlpExporterCompressionEnum: string
{
    case None = 'none';
    case Gzip = 'gzip';
}
