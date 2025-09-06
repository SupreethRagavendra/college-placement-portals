-- Create student_results table for Supabase
-- Run this in your Supabase SQL editor

CREATE TABLE IF NOT EXISTS public.student_results (
    id BIGSERIAL PRIMARY KEY,
    student_id BIGINT NOT NULL,
    assessment_id BIGINT NOT NULL REFERENCES public.assessments(id) ON DELETE CASCADE,
    score INTEGER NOT NULL DEFAULT 0,
    total_questions INTEGER NOT NULL DEFAULT 0,
    time_taken INTEGER NOT NULL DEFAULT 0, -- in seconds
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_student_results_student_id ON public.student_results(student_id);
CREATE INDEX IF NOT EXISTS idx_student_results_assessment_id ON public.student_results(assessment_id);
CREATE INDEX IF NOT EXISTS idx_student_results_submitted_at ON public.student_results(submitted_at);

-- Enable Row Level Security (RLS)
ALTER TABLE public.student_results ENABLE ROW LEVEL SECURITY;

-- Create policies for student_results table
CREATE POLICY "Students can view their own results" ON public.student_results
    FOR SELECT USING (auth.uid()::text = student_id::text);

CREATE POLICY "Students can insert their own results" ON public.student_results
    FOR INSERT WITH CHECK (auth.uid()::text = student_id::text);

CREATE POLICY "Admins can view all results" ON public.student_results
    FOR ALL USING (auth.role() = 'authenticated');
