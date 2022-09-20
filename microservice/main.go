package main

import (
	"context"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"time"

	log "github.com/sirupsen/logrus"

	"go.opentelemetry.io/contrib/instrumentation/net/http/otelhttp"
	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/attribute"
	"go.opentelemetry.io/otel/exporters/otlp/otlptrace/otlptracehttp"
	"go.opentelemetry.io/otel/propagation"
	"go.opentelemetry.io/otel/sdk/resource"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	semconv "go.opentelemetry.io/otel/semconv/v1.4.0"
	"go.opentelemetry.io/otel/trace"
)

const name = "microservice"

var tracer trace.Tracer

func init() {
	tracer = otel.Tracer("io.opentelemetry.contrib.go")

	ctx := context.Background()
	traceExporter, err := otlptracehttp.New(ctx)
	if err != nil {
		log.Fatal(err)
	}

	res, err := resource.New(ctx,
		resource.WithAttributes(
			semconv.ServiceNameKey.String("microservice"),
		),
	)
	if err != nil {
		log.Fatal(err)
	}

	bsp := sdktrace.NewBatchSpanProcessor(traceExporter)
	tracerProvider := sdktrace.NewTracerProvider(
		sdktrace.WithSampler(sdktrace.AlwaysSample()),
		sdktrace.WithResource(res),
		sdktrace.WithSpanProcessor(bsp),
	)
	otel.SetTracerProvider(tracerProvider)
	otel.SetTextMapPropagator(propagation.TraceContext{})
}

// sleepy mocks work that your application does.
func sleepy(ctx context.Context) {
	_, span := tracer.Start(ctx, "sleep")
	defer span.End()

	spanContext := span.SpanContext()
	log.WithFields(log.Fields{
		"traceId": spanContext.TraceID().String(),
	}).Info("Start complex work")

	sleepTime := 1 * time.Second
	time.Sleep(sleepTime)
	span.SetAttributes(attribute.Int("sleep.duration", int(sleepTime)))

	log.WithFields(log.Fields{
		"traceId": spanContext.TraceID().String(),
	}).Info("End complex work")
}

func endpoint1(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	resp := make(map[string]string)
	resp["message"] = "Data from remote microservice."
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		log.Fatalf("Error happened in JSON marshal. Err: %s", err)
	}
	w.Write(jsonResp)

	ctx := r.Context()
	sleepy(ctx)
}

func endpoint2(w http.ResponseWriter, r *http.Request) {
	span := trace.SpanFromContext(r.Context())
	spanContext := span.SpanContext()
	log.WithFields(log.Fields{
		"traceId": spanContext.TraceID().String(),
	}).Error("Endpoint 2 is not implemented yet.")

	w.WriteHeader(404)
}

func main() {
	logFile := "logs/microservice.log"
	f, err := os.OpenFile(logFile, os.O_WRONLY|os.O_CREATE|os.O_APPEND, 0644)
	if err != nil {
		fmt.Println("Failed to create logfile" + logFile)
		panic(err)
	}
	defer f.Close()
	// Output to stdout instead of the default stderr
	log.SetOutput(f)

	// Only log the debug severity or above
	log.SetLevel(log.DebugLevel)
	log.SetFormatter(&log.JSONFormatter{})

	// Endpoint 1
	endpoint1Handler := http.HandlerFunc(endpoint1)
	endpoint1wrappedHandler := otelhttp.NewHandler(endpoint1Handler, "endpoint1")
	http.Handle("/endpoint1", endpoint1wrappedHandler)

	// Endpoint 2
	endpoint2Handler := http.HandlerFunc(endpoint2)
	endpoint2WrappedHandler := otelhttp.NewHandler(endpoint2Handler, "endpoint2")
	http.Handle("/endpoint2", endpoint2WrappedHandler)

	// And start the HTTP serve.
	log.Fatal(http.ListenAndServe(":8080", nil))
}
