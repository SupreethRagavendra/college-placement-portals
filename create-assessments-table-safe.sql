-- Create assessments table for Supabase (Safe version - handles existing objects)
-- Run this in your Supabase SQL editor

-- Create assessments table
CREATE TABLE IF NOT EXISTS public.assessments (
    id BIGSERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    category TEXT NOT NULL CHECK (category IN ('Aptitude', 'Technical')),
    time_limit INTEGER NOT NULL DEFAULT 30 CHECK (time_limit > 0 AND time_limit <= 300),
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create assessment_questions junction table
CREATE TABLE IF NOT EXISTS public.assessment_questions (
    id BIGSERIAL PRIMARY KEY,
    assessment_id BIGINT NOT NULL REFERENCES public.assessments(id) ON DELETE CASCADE,
    question_id BIGINT NOT NULL REFERENCES public.questions(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(assessment_id, question_id)
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_assessments_category ON public.assessments(category);
CREATE INDEX IF NOT EXISTS idx_assessments_active ON public.assessments(is_active);
CREATE INDEX IF NOT EXISTS idx_assessment_questions_assessment_id ON public.assessment_questions(assessment_id);
CREATE INDEX IF NOT EXISTS idx_assessment_questions_question_id ON public.assessment_questions(question_id);

-- Enable Row Level Security (RLS)
ALTER TABLE public.assessments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.assessment_questions ENABLE ROW LEVEL SECURITY;

-- Drop existing policies if they exist, then create new ones
DROP POLICY IF EXISTS "Allow all operations for authenticated users" ON public.assessments;
DROP POLICY IF EXISTS "Allow all operations for authenticated users" ON public.assessment_questions;

-- Create policies for assessments table
CREATE POLICY "Allow all operations for authenticated users" ON public.assessments
    FOR ALL USING (auth.role() = 'authenticated');

-- Create policies for assessment_questions table  
CREATE POLICY "Allow all operations for authenticated users" ON public.assessment_questions
    FOR ALL USING (auth.role() = 'authenticated');

-- Insert some sample assessments (only if they don't exist)
INSERT INTO public.assessments (name, description, category, time_limit, is_active) 
SELECT * FROM (VALUES
('Basic Aptitude Test', 'General aptitude questions covering logical reasoning, mathematics, and verbal ability', 'Aptitude', 30, true),
('Technical Programming Test', 'Programming and technical questions for software development roles', 'Technical', 45, true),
('Advanced Aptitude Assessment', 'Challenging aptitude questions for senior positions', 'Aptitude', 60, true),
('System Design Interview', 'Technical questions covering system design and architecture', 'Technical', 90, true)
) AS v(name, description, category, time_limit, is_active)
WHERE NOT EXISTS (SELECT 1 FROM public.assessments WHERE assessments.name = v.name);
